<?php

namespace Modules\Api\Http\Controllers\Voip;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Hekmatinasser\Verta\Verta;
use Modules\User\Entities\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\Api\app\Models\VoipIncoming;
use Modules\Api\app\Models\VoipVoiceRecord;
use Modules\Front\app\Models\FeedBack;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\Transaction\Enum\TransactionPaidEnum;
use Modules\Transaction\Enum\TransactionStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentVia;
use Modules\AppointmentUser\Enum\model\UserModel;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\Enum\model\AppointmentModel;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\model\UserModelAppointment;
use Modules\AppointmentSetting\app\Enum\AppintmentSettingDayNumber;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\Api\Http\Controllers\Appointment\AppointmentApiController;
use Modules\User\app\Notifications\CustomLinkToUserSmsNotification;

class VoipController extends Controller
{
    use ApiHandlerTrait;

    private const ERROR_APPOINTMENT_NOT_FOUND = 9;
    private const ERROR_OPERATOR_NOT_FOUND = 31;
    private const ERROR_WORKING_TIME_IS_OVER = 32;
    private const ERROR_PAYMENT_APPOINTMENT_NOT_FOUND = 41;
    private const ERROR_PAYMENT_NOT_ACTIVE = 42;
    private const ERROR_PAYMENT_ALREADY_DONE = 43;

    private const ERROR_TEXT = [
        self::ERROR_APPOINTMENT_NOT_FOUND => 'نوبتی با شماره وارد شده یافت نشد',
        self::ERROR_OPERATOR_NOT_FOUND => 'هیچ اپراتوری یافت نشد',
        self::ERROR_WORKING_TIME_IS_OVER => 'زمان کاری منشی/اپراتور به اتمام رسیده',
        self::ERROR_PAYMENT_APPOINTMENT_NOT_FOUND => 'نوبتی یافت نشد',
        self::ERROR_PAYMENT_NOT_ACTIVE => 'پرداخت برای این نوبت فعال نیست',
        self::ERROR_PAYMENT_ALREADY_DONE => 'پرداخت قبلا انجام شده است',
    ];

    public function checkDoctorAppointment(Request $request)
    {
        $doctorId = $request->input('doctorId');

        if (blank($doctorId) || ! ctype_digit((string) $doctorId)) {
            return $this->ok([
                'status' => false,
                'message' => 'شناسه پزشک معتبر نیست',
                'error_code' => 1,
            ]);
        }

        $doctor = User::find((int) $doctorId);

        if (! $doctor) {
            return $this->ok([
                'status' => false,
                'message' => 'پزشک یافت نشد',
                'error_code' => 2,
            ]);
        }

        return $this->ok([
            'status' => $doctor->appointmentSettings()->active()->exists(),
        ]);
    }

    public function getAppointmentDoctors(Request $request)
    {
        $doctors = User::doctors_query()
            ?->whereHas('appointmentSettings', fn($query) => $query->active())
            ->get()
            ->map(fn(User $user) => [
                'id' => $user->id,
                'name' => trim($user->fullName) ?: $user->mobile,
            ])
            ->values()
            ->all() ?? [];

        return $this->ok([
            'doctors' => $doctors,
        ]);
    }

    public function getAppointmentOfficesAndParts(Request $request)
    {
        $doctor = User::findOrFail($request->get('doctorId'));
        $result = [];

        foreach ($doctor->appointmentSettings()->active()->with(['place', 'service'])->get() as $setting) {
            $placeId = $setting->place_id ?? 0;

            if (! isset($result[$placeId])) {
                $result[$placeId] = [
                    'appointmentOfficeId' => $setting->place_id,
                    'appointmentOfficeName' => $setting->place?->title ?? 'عمومی',
                    'appointmentParts' => [],
                ];
            }

            $result[$placeId]['appointmentParts'][] = [
                'appointmentPartId' => $setting->service_id,
                'appointmentPartName' => $setting->service?->title ?? 'عمومی',
                'multiSelectArea' => false,
                'areas' => [],
            ];
        }

        return $this->ok(array_values($result));
    }

    public function checkAppointment(Request $request)
    {
        $appointmentSetting = $this->findSettingFromOldRequest($request);
        $status = $appointmentSetting?->checkActive() && (bool) data_get($appointmentSetting->detail, AppointmentSetting::VISIT_TYPE_INPERSON, true);

        return $this->ok([
            'status' => (bool) $status,
            'message' => ! $status ? 'نوبت دهی برای این پزشک غیر فعال است' : null,
        ]);
    }

    public function getAppointmentTimes(Request $request)
    {
        $appointmentSetting = $this->findSettingFromOldRequest($request);
        if (! $appointmentSetting) {
            return $this->ok([
                'status' => false,
                'error' => 'مشکلی در سیستم به وجود آمده است! لطفا با پشتیبانی تماس حاص فرمایید.',
            ]);
        }

        if (! $this->isVoipVisitActive($appointmentSetting)) {
            return $this->ok([]);
        }

        $listDays = $this->appointmentList($appointmentSetting);

        return $this->ok($this->oldTimesPayload($listDays, $request->get('timeFilter'), $appointmentSetting));
    }

    public function getAppointmentUser(Request $request)
    {
        $appointmentUser = AppointmentUser::find($request->input('appointmentCode'));
        if (! $appointmentUser) {
            return $this->oldError(self::ERROR_APPOINTMENT_NOT_FOUND);
        }

        $nationalCode = $request->input('nationalCode');
        if ($nationalCode && $appointmentUser->user?->national_code !== $nationalCode && $appointmentUser->user?->nationalCode !== $nationalCode) {
            return $this->oldError(self::ERROR_APPOINTMENT_NOT_FOUND);
        }

        return $this->ok([
            'status' => true,
            'timestamp' => Carbon::parse($appointmentUser->date_visit, 'Asia/Tehran')->timestamp,
            'active_payment_by_phone' => false,
            'price' => data_get($appointmentUser->details, AppointmentUser::DETAIL_PAYMENT . '.' . AppointmentUser::DETAIL_PAYMENT_PRICE . '.int', 0),
        ]);
    }

    public function cancelAppointmentUser(Request $request)
    {
        $appointmentUser = AppointmentUser::find($request->input('appointmentCode'));
        if (! $appointmentUser) {
            return $this->oldError(self::ERROR_APPOINTMENT_NOT_FOUND);
        }

        if ($appointmentUser->status !== AppointmentUserStatusEnum::STATUS_CANCEL) {
            $appointmentUser->update([
                'status' => AppointmentUserStatusEnum::STATUS_CANCEL,
            ]);
            $appointmentUser->setting?->runGenerateCacheJob($appointmentUser->date_visit);
        }

        return $this->ok([
            'status' => true,
        ]);
    }

    public function onlineVisit(Request $request)
    {
        $user = User::where('mobile', 'LIKE', '%' . $request->get('mobile') . '%')->first();
        $returnCode = 2;
        $dateVisitTimestamp = null;
        $doctorId = null;
        $res = [];
        $appointmentUser = null;

        if ($user) {
            $activeAppointment = AppointmentUser::query()
                ->where('user_id', $user->id)
                ->where('kind', AppointmentUserKindEnum::ONLINE)
                ->whereIn('status', [AppointmentUserStatusEnum::STATUS_SUCCESSFUL, AppointmentUserStatusEnum::STATUS_ATTENDED])
                ->orderByDesc('date_visit')
                ->first();

            if ($activeAppointment) {
                $dateVisit = Carbon::parse($activeAppointment->date_visit, 'Asia/Tehran');
                $dateVisitTimestamp = $dateVisit->timestamp;
                $endVisit = $dateVisit->copy()->setTimeFromTimeString($activeAppointment->end_time ?? $dateVisit->copy()->addMinutes(30)->toTimeString());

                if (now('Asia/Tehran')->between($dateVisit, $endVisit)) {
                    $returnCode = 3;
                    $doctorId = $activeAppointment->doctor_id;
                    $appointmentUser = $activeAppointment;
                }
            }

            if ($returnCode === 2) {
                $appointmentUser = AppointmentUser::query()
                    ->where('user_id', $user->id)
                    ->where('kind', AppointmentUserKindEnum::ONLINE)
                    ->whereIn('status', [AppointmentUserStatusEnum::STATUS_SUCCESSFUL, AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT])
                    ->where('date_visit', '>', now('Asia/Tehran'))
                    ->orderBy('date_visit')
                    ->first();

                if ($appointmentUser) {
                    $returnCode = 1;
                    $dateVisitTimestamp = Carbon::parse($appointmentUser->date_visit, 'Asia/Tehran')->timestamp;
                    $timeDifference = $dateVisitTimestamp - time();

                    if ($timeDifference > 86400) {
                        $res = ['type' => 'day', 'day' => floor($timeDifference / 86400)];
                    } elseif ($timeDifference > 3600) {
                        $res = ['type' => 'hour', 'hour' => floor($timeDifference / 3600)];
                    } else {
                        $res = ['type' => 'minute', 'minute' => floor($timeDifference / 60)];
                    }
                }
            }
        }

        return $this->ok([
            'status' => true,
            'code' => $returnCode,
            'doctor_id' => $doctorId,
            'appointment' => $appointmentUser,
            'date_visit' => $dateVisitTimestamp,
            'res' => $res,
        ]);
    }

    public function connectToOperator(Request $request)
    {
        $operatorId = $request->input('operator_id');
        $operator = User::find($operatorId);

        if (! $operator || ! $operator->isOperator()) {
            return $this->oldError(self::ERROR_OPERATOR_NOT_FOUND);
        }

        $now = now('Asia/Tehran');
        $dayNumber = AppintmentSettingDayNumber::getConstant(strtolower($now->format('l')))->value;
        $currentTime = $now->format('H:i:s');

        $isWithinWorkingTime = $operator->operatorTimes()
            ->where('day_number', $dayNumber)
            ->whereTime('start_at', '<=', $currentTime)
            ->whereTime('end_at', '>=', $currentTime)
            ->exists();

        if (! $isWithinWorkingTime) {
            return $this->oldError(self::ERROR_WORKING_TIME_IS_OVER);
        }

        return $this->ok([
            'status' => true,
        ]);
    }

    public function incomingCall(Request $request)
    {
        if ($request->filled('incoming')) {
            VoipIncoming::create([
                'incoming' => (string) $request->input('incoming'),
            ]);
        }

        return $this->ok([
            'status' => true,
        ]);
    }
    public function paymentSendSecondPassword(Request $request)
    {
        $appointmentUser = AppointmentUser::find((int) $request->get('appointment_id'));
        if (! $appointmentUser) {
            return $this->oldError(self::ERROR_PAYMENT_APPOINTMENT_NOT_FOUND);
        }

        if ($appointmentUser->transaction?->status === TransactionStatusEnum::SUCCESSFUL) {
            return $this->oldError(self::ERROR_PAYMENT_ALREADY_DONE);
        }

        return $this->oldError(self::ERROR_PAYMENT_NOT_ACTIVE);
    }

    public function paymentByVoip(Request $request)
    {
        $appointmentUser = AppointmentUser::find((int) $request->get('appointment_id'));
        if (! $appointmentUser) {
            return $this->oldError(self::ERROR_PAYMENT_APPOINTMENT_NOT_FOUND);
        }

        if (! data_get($appointmentUser->details, AppointmentUser::DETAIL_PAYMENT . '.status')) {
            return $this->oldError(self::ERROR_PAYMENT_NOT_ACTIVE);
        }

        if ($appointmentUser->transaction?->status === TransactionStatusEnum::SUCCESSFUL) {
            return $this->oldError(self::ERROR_PAYMENT_ALREADY_DONE);
        }

        $price = (int) data_get($appointmentUser->details, AppointmentUser::DETAIL_PAYMENT . '.' . AppointmentUser::DETAIL_PAYMENT_PRICE . '.int', 0);

        $transaction = $appointmentUser->transaction()->updateOrCreate(
            ['transactionable_id' => $appointmentUser->id],
            [
                'user_id' => $appointmentUser->user_id,
                'transaction_code' => $appointmentUser->transaction?->transaction_code ?? \Modules\Transaction\app\Models\Transaction::generateTransactionCode(),
                'status' => TransactionStatusEnum::SUCCESSFUL,
                'paid_by' => TransactionPaidEnum::BY_ADMIN,
                'cost' => $price,
                'total_cost' => $price,
                'detail' => [
                    'source' => 'voip',
                    'card_number' => $request->get('card_number'),
                ],
            ]
        );

        $appointmentUser->update([
            'status' => AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
        ]);

        return $this->ok([
            'status' => true,
            'message' => 'پرداخت با موفقیت ثبت شد',
            'data' => [
                'tracking_code' => $transaction->transaction_code,
            ],
        ]);
    }

    public function sendCustomLink(Request $request)
    {
        $request->merge([
            'phone_number' => convert2english($request->input('phone_number')),
        ]);

        $validated = $request->validate([
            'phone_number' => [
                'bail',
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! checkMobileNumber($value)) {
                        $fail('شماره موبایل وارد شده معتبر نیست.');
                    }
                },
            ],
        ]);

        $template = setting(SettingKeyEnum::SMS_CUSTOM_LINK_TO_USER_TEMPLATE);
        if (blank($template)) {
            return $this->requestException([
                'status' => false,
                'message' => 'الگوی پیامک ارسال لینک سفارشی تنظیم نشده است.',
            ]);
        }

        $phoneNumber = $this->normalizeMobileNumber($validated['phone_number']);

        Notification::route('sms', $phoneNumber)->notify(
            new CustomLinkToUserSmsNotification(
                receptor: $phoneNumber,
                template: $template,
                siteTitle: (string) setting(SettingKeyEnum::SITE_TITLE),
            )
        );

        return $this->ok([
            'status' => true,
            'message' => 'پیامک لینک سفارشی در صف ارسال قرار گرفت.',
        ]);
    }

    public function sendCustomLink2(Request $request)
    {
        $request->merge([
            'phone_number' => convert2english($request->input('phone_number')),
        ]);

        $validated = $request->validate([
            'phone_number' => [
                'bail',
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! checkMobileNumber($value)) {
                        $fail('شماره موبایل وارد شده معتبر نیست.');
                    }
                },
            ],
        ]);

        $template = setting(SettingKeyEnum::SMS_CUSTOM_LINK_2_TO_USER_TEMPLATE);
        if (blank($template)) {
            return $this->requestException([
                'status' => false,
                'message' => 'الگوی پیامک لینک سفارشی ۲ تنظیم نشده است.',
            ]);
        }

        $phoneNumber = $this->normalizeMobileNumber($validated['phone_number']);

        Notification::route('sms', $phoneNumber)->notify(
            new CustomLinkToUserSmsNotification(
                receptor: $phoneNumber,
                template: $template,
                siteTitle: (string) setting(SettingKeyEnum::SITE_TITLE),
            )
        );

        return $this->ok([
            'status' => true,
            'message' => 'پیامک لینک سفارشی ۲ در صف ارسال قرار گرفت.',
        ]);
    }

    public function storeSurvey(Request $request)
    {
        $request->validate([
            'appointmentCode' => 'required|integer',
            'score' => 'nullable|integer|between:1,5',
            'file' => 'nullable|mimes:wav',
        ]);

        $appointmentUser = AppointmentUser::find($request->input('appointmentCode'));
        if (! $appointmentUser) {
            return $this->oldError(self::ERROR_APPOINTMENT_NOT_FOUND);
        }

        $filename = '';
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            if (! is_dir(public_path('uploads/voip'))) {
                mkdir(public_path('uploads/voip'), 0755, true);
            }
            $file->move(public_path('uploads/voip'), $filename);
        }

        $details = $appointmentUser->details ?? [];
        $details[AppointmentUser::DETAIL_SURVEY] = [
            AppointmentUser::DETAIL_SURVEY => $request->input('score'),
            AppointmentUser::DETAIL_SURVEY_FEEDBACK_FILE => $filename,
        ];
        $appointmentUser->update([
            'details' => $details,
        ]);

        if ($request->filled('score')) {
            FeedBack::updateOrCreate(
                ['appointment_user_id' => $appointmentUser->id, 'question' => 1],
                ['answer' => (int) $request->input('score')]
            );
        }

        return $this->ok([
            'status' => true,
            'error' => $filename,
        ]);
    }

    public function storeVoiceRecord(Request $request)
    {
        $request->merge([
            'incoming' => convert2english((string) $request->input('incoming')),
        ]);

        $validated = $request->validate([
            'file' => 'required|file',
            'incoming' => 'required|string|max:30',
            'name' => 'nullable|string|max:255',
        ]);

        $incoming = $validated['incoming'];
        $file = $request->file('file');
        $directory = public_path('uploads/voip/voice_records');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = $file->getClientOriginalExtension();
        $filename = now('Asia/Tehran')->format('YmdHis') . '_' . Str::random(16) . ($extension ? ".{$extension}" : '');
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $destinationPath = $directory . DIRECTORY_SEPARATOR . $filename;

        $file->move($directory, $filename);
        $fileSize = is_file($destinationPath) ? filesize($destinationPath) : null;

        $user = User::firstOrCreate(
            ['mobile' => $incoming],
            ['password' => User::generatePassword()]
        );

        $voiceRecord = VoipVoiceRecord::create([
            'user_id' => $user->id,
            'incoming' => $incoming,
            'name' => $validated['name'] ?? null,
            'file_path' => 'uploads/voip/voice_records/' . $filename,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $fileSize,
        ]);

        return $this->created([
            'status' => true,
            'message' => 'فایل صوتی با موفقیت ذخیره شد',
            'data' => [
                'voice_record_id' => $voiceRecord->id,
                'user_id' => $user->id,
                'incoming' => $incoming,
                'file' => url($voiceRecord->file_path),
            ],
        ]);
    }

    private function oldError(int $errorCode)
    {
        return $this->ok([
            'status' => false,
            'message' => self::ERROR_TEXT[$errorCode] ?? 'خطا',
            'error_code' => $errorCode,
        ]);
    }

    private function normalizeMobileNumber(string $phoneNumber): string
    {
        $phoneNumber = ltrim($phoneNumber, '+');

        return str_starts_with($phoneNumber, '98')
            ? '0' . substr($phoneNumber, 2)
            : $phoneNumber;
    }

    private function findSettingFromOldRequest(Request $request): ?AppointmentSetting
    {
        $doctorId = $request->get('doctorId') ?? $request->get('doctor_id');
        $placeId = $request->get('appointmentOfficeId') ?? $request->get('place_id') ?? $request->get('places_id');
        $serviceId = $request->get('appointmentPartId') ?? $request->get('service_id') ?? $request->get('services_id');
        Log::info('place => ' . $request->get('appointmentOfficeId') . ' service => ' . $request->get('appointmentPartId'));
        if (! $doctorId) {
            return null;
        }
        $appointmentSetting =  AppointmentSetting::query()
            ->active()
            ->where('user_id', $doctorId)
            ->when($placeId, fn($query) => $query->where('place_id', $placeId))
            ->when(! $placeId, fn($query) => $query->whereNull('place_id'))
            ->when($serviceId, fn($query) => $query->where('service_id', $serviceId))
            ->when(! $serviceId, fn($query) => $query->whereNull('service_id'))
            ->first()
            ?? AppointmentSetting::query()
            ->active()
            ->where('user_id', $doctorId)
            ->whereNull('place_id')
            ->whereNull('service_id')
            ->first();
        Log::info('appointmentSetting lookup completed', [
            'appointment_setting_id' => $appointmentSetting?->id,
            'doctor_id' => $doctorId,
            'place_id' => $placeId,
            'service_id' => $serviceId,
        ]);
        return $appointmentSetting;
    }

    private function appointmentList(AppointmentSetting $appointmentSetting): array
    {
        // if (env('APPOINTMENT_SANDBOX') || config('app.without_cache')) {
        //     Cache::forget('appointmentList.' . $appointmentSetting->id);
        // }

        // return Cache::rememberForever('appointmentList.' . $appointmentSetting->id, function () use ($appointmentSetting) {
        //     $appointmentSetting->update(['updated_log_at' => now()]);

            return app('AppointmentUserService')->listAppointments($appointmentSetting, $this->appointmentListDetails($appointmentSetting));
        // });
    }

    private function appointmentListDetails(AppointmentSetting $appointmentSetting): array
    {
        if (! $appointmentSetting->first_day_active) {
            return [];
        }

        return [
            'completeDays' => true,
            'specialDays' => Carbon::parse($appointmentSetting->first_day_active, 'Asia/Tehran')->toDateString(),
            'numberDays' => max(60, (int) ($appointmentSetting->max_day_active ?? 0)),
        ];
    }

    private function isVoipVisitActive(AppointmentSetting $appointmentSetting): bool
    {
        return filter_var(
            data_get($appointmentSetting->detail, AppointmentSetting::VISIT_TYPE_VOIP, false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    private function oldTimesPayload(array $data, ?string $timeFilter, AppointmentSetting $appointmentSetting): array
    {
        $result = [];
        $resultDays = 1;
        $emptyAppointmentDisplayLimit = $appointmentSetting->emptyAppointmentDisplayLimit();
        $maxAvailableDate = $this->maxAvailableAppointmentDate($appointmentSetting);

        foreach ($data['data'] as $year => $months) {
            foreach ($months as $month => $days) {
                foreach ($days as $day => $appointment) {
                    if (! $this->passesFrontEmptyAppointmentDayFilters($data, $appointment, $appointmentSetting)) {
                        continue;
                    }

                    if ($maxAvailableDate && Carbon::parse($appointment['day_number_gmt'], 'Asia/Tehran')->startOfDay()->gt($maxAvailableDate)) {
                        break 3;
                    }

                    $displayedEmptyAppointments = 0;
                    foreach ($appointment['times'] as $time) {
                        if (! ($time['status'] ?? false) || ! isset($time['timestamp'])) {
                            continue;
                        }

                        $appointmentTime = Carbon::createFromTimestamp((int) $time['timestamp'], 'Asia/Tehran');
                        if (! $this->isFrontSelectableAppointmentTime($appointmentTime)) {
                            continue;
                        }

                        $hour = (int) $appointmentTime->format('H');
                        $period = $hour < 12 ? 'am' : 'pm';

                        if ($timeFilter && in_array($timeFilter, ['am', 'pm'], true) && $period !== $timeFilter) {
                            continue;
                        }

                        if ($emptyAppointmentDisplayLimit !== null && $displayedEmptyAppointments >= $emptyAppointmentDisplayLimit) {
                            continue;
                        }

                        $result['day' . $resultDays][$period]['times'][] = [
                            'timestamp' => $time['timestamp'],
                        ];
                        $displayedEmptyAppointments++;
                    }

                    if (isset($result['day' . $resultDays])) {
                        $resultDays++;
                    }

                    if ($resultDays > 10) {
                        break 3;
                    }
                }
            }
        }

        Log::info('Voip oldTimesPayload returned times', [
            'appointment_setting_id' => $appointmentSetting->id,
            'time_filter' => $timeFilter,
            'day_count' => count($result),
            'time_count' => $this->countOldTimesPayloadTimes($result),
        ]);

        return $result;
    }

    private function countOldTimesPayloadTimes(array $result): int
    {
        $count = 0;
        foreach ($result as $day) {
            foreach (['am', 'pm'] as $period) {
                $count += count($day[$period]['times'] ?? []);
            }
        }

        return $count;
    }

    private function getListEmptyAppointment($data, AppointmentSetting $appointmentSetting)
    {
        $report = $data['report'];
        $mainDaActive = $report['min_day_active'];

        $isDay = verta()->addDays($mainDaActive)->day;
        $isMonth = verta()->addDays($mainDaActive)->month;
        $isYear = verta()->addDays($mainDaActive)->year;

        $result = [];
        $maxDay = 15;
        $DaysDisplayed = 0;
        $dayCount = 0;
        $emptyAppointmentDisplayLimit = $appointmentSetting->emptyAppointmentDisplayLimit();
        $maxAvailableDate = $this->maxAvailableAppointmentDate($appointmentSetting);
        foreach ($data['data'] as $yeay => $day) {
            if ($yeay < $isYear) {
                continue;
            }
            foreach ($day as $month => $appointments) {

                foreach ($appointments as $day => $appointment) {

                    if ($day < $isDay && $month < $isMonth && $yeay < $isYear) {
                        continue;
                    }
                    if (! $this->passesFrontEmptyAppointmentDayFilters($data, $appointment, $appointmentSetting)) {
                        continue;
                    }

                    if ($maxAvailableDate && Carbon::parse($appointment['day_number_gmt'], 'Asia/Tehran')->startOfDay()->gt($maxAvailableDate)) {
                        break 3;
                    }

                    $availableTimes = [];
                    foreach ($appointment['times'] ?? [] as $time) {
                        if (! ($time['status'] ?? false) || ! isset($time['timestamp'])) {
                            continue;
                        }

                        $appointmentTime = Carbon::createFromTimestamp((int) $time['timestamp'], 'Asia/Tehran');
                        if (! $this->isFrontSelectableAppointmentTime($appointmentTime)) {
                            continue;
                        }

                        if ($emptyAppointmentDisplayLimit !== null && count($availableTimes) >= $emptyAppointmentDisplayLimit) {
                            continue;
                        }

                        $availableTimes[] = ['timestamp' => $time['timestamp']];
                    }

                    if ($availableTimes === []) {
                        continue;
                    }

                    $DaysDisplayed++;

                    if ($DaysDisplayed > $maxDay) {
                        break 3;
                    }
                    $dayCount++;
                    $result['day' . $dayCount]['times'] = $availableTimes;
                }
            }
        }
        return $result;
    }

    private function passesFrontEmptyAppointmentDayFilters(array $listOfAppointment, array $appointment, AppointmentSetting $appointmentSetting): bool
    {
        $mainDayActive = (int) ($listOfAppointment['report']['min_day_active'] ?? 0);
        $appointmentDate = Carbon::parse($appointment['day_number_gmt'], 'Asia/Tehran')->startOfDay();
        $minActiveDate = Carbon::now('Asia/Tehran')->addDays($mainDayActive)->startOfDay();

        if ($appointmentDate->lt($minActiveDate)) {
            return false;
        }

        if ($appointmentDate->lt(Carbon::now('Asia/Tehran')->startOfDay())) {
            return false;
        }

        if (($appointment['empty_appoints'] ?? 0) <= 0 || ($appointment['status'] ?? false) == false || ($appointment['user_status'] ?? false) == false) {
            return false;
        }

        $firstDayActive = $listOfAppointment['report']['first_day_active'] ?? $appointmentSetting->first_day_active;
        if ($firstDayActive !== null && Carbon::parse($firstDayActive, 'Asia/Tehran')->startOfDay()->gt($appointmentDate)) {
            return false;
        }

        return true;
    }

    private function maxAvailableAppointmentDate(AppointmentSetting $appointmentSetting): ?Carbon
    {
        if (! isset($appointmentSetting->max_day_active)) {
            return null;
        }

        $now = Carbon::now('Asia/Tehran');
        $maxAvailableDate = $now->copy()->startOfDay()->addDays((int) $appointmentSetting->max_day_active);
        $openTime = data_get($appointmentSetting->detail, AppointmentSetting::OPEN_TIME, '00:00');

        if (! empty($openTime)) {
            try {
                $openAt = $now->copy()->setTimeFromTimeString($openTime);
                if ($now->lt($openAt)) {
                    $maxAvailableDate->subDay();
                }
            } catch (\Throwable $exception) {
                // Invalid stored time should not block appointments; keep the old midnight behavior.
            }
        }

        return $maxAvailableDate;
    }

    private function isFrontSelectableAppointmentTime(Carbon $appointmentTime): bool
    {
        if ($appointmentTime->copy()->isToday()) {
            return $appointmentTime->gt(Carbon::now('Asia/Tehran')->addMinutes(30));
        }

        return $appointmentTime->isFuture();
    }

    public function listDays(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        $placesId = $request->get('places_id');
        $servicesId = $request->get('services_id');

        $appointmentApiController = new AppointmentApiController();
        $findAlterNateDoctor = $appointmentApiController->findAlterNateDoctor();


        if ($findAlterNateDoctor == null) {
            return null;
        }
        $appointmentSetting = AppointmentSetting::where('user_id', $findAlterNateDoctor->id);

        if ($placesId) {
            $appointmentSetting->where('place_id', $placesId);
        } else {
            $appointmentSetting->whereNull('place_id');
        }
        if ($servicesId) {
            $appointmentSetting->where('service_id', $servicesId);
        } else {
            $appointmentSetting->whereNull('service_id');
        }
        $appointmentSetting = $appointmentSetting->first();

        if (!$appointmentSetting) {
            return $this->requestException([
                'status' => false,
                'message' => 'هیچ اطلاعاتی یاف تشد'
            ]);
        }

        if (! $this->isVoipVisitActive($appointmentSetting)) {
            return $this->ok([
                'doctor_selected' => $findAlterNateDoctor->id,
                'appointment_setting_id' => $appointmentSetting->id,
                'empty_times' => [],
            ]);
        }


        if (env('APPOINTMENT_SANDBOX')) {
            Cache::forget('appointmentList.' . $appointmentSetting->id);
        }
        $details = $this->appointmentListDetails($appointmentSetting);
        $listDays =  app('AppointmentUserService')->listAppointments($appointmentSetting, $details);
        return $this->ok(
            [
                'doctor_selected' => $findAlterNateDoctor->id,
                'appointment_setting_id' => $appointmentSetting->id,
                'empty_times' => $this->getListEmptyAppointment($listDays, $appointmentSetting),
            ]
        );
    }

    public function storeAppointment(Request $request)
    {
        $doctorId            = $request->get('doctor_id') ?? $request->get('doctorId');
        $visitDate           = $request->get('visit_date') ?? $request->get('timestamp');
        $mobile              = $request->get('user_mobile') ?? $request->get('mobile');
        $placesId            = $request->get('place_id') ?? $request->get('places_id') ?? $request->get('appointmentOfficeId');
        $servicesId          = $request->get('service_id') ?? $request->get('services_id') ?? $request->get('appointmentPartId');
        $operatorId          = $request->get('operator_id');
        $kindParameter       = $request->get('kind');
        $description         = $request->get('description');
        $nationalCode        = $request->get('nationalCode') ?? $request->get('national_code');

        if (empty($doctorId) || empty($visitDate) || empty($mobile)) {
            return $this->requestException([
                'status' => false,
                'message' => ' پزشک و تاریخ نوبت , شماره همراه کاربر الزامی است.'
            ]);
        }

        $doctor = User::find($doctorId);
        if (! $doctor) {
            return $this->requestException([
                'status' => false,
                'message' => 'پزشک یافت نشد.'
            ]);
        }

        $startDate = Carbon::createFromTimestamp($visitDate, 'Asia/Tehran');
        if ($startDate->isPast()) {
            return $this->requestException([
                'status' => false,
                'message' => 'تاریخ انتخابی صحیح نیست.'
            ]);
        }

        $appointmentSetting = $doctor->appointmentSettings()->active()
            ->when(isset($servicesId), function ($q) use ($servicesId) {
                return $q->where(function ($query) use ($servicesId) {
                    $query->where('service_id', $servicesId)->orWhereNull('service_id');
                });
            })->when(! isset($servicesId), function ($q) {
                return $q->whereNull('service_id');
            })->when(isset($placesId), function ($q) use ($placesId) {
                return $q->where(function ($query) use ($placesId) {
                    $query->where('place_id', $placesId)->orWhereNull('place_id');
                });
            })->when(! isset($placesId), function ($q) {
                return $q->whereNull('place_id');
            })->when(isset($operatorId), function ($q) use ($operatorId) {
                return $q->where(function ($query) use ($operatorId) {
                    $query->whereJsonContains('detail->operators->ids', (string) $operatorId)
                        ->orWhereJsonContains('detail->operators->ids', (int) $operatorId);
                });
            })->first();


        if (! isset($appointmentSetting)) {
            return $this->requestException([
                'status' => false,
                'message' => 'تنظیمات مربوط به پزشک پیدا نشد ، بخش،مطب،یا اپراتور را بررسی کنید.'
            ]);
        }
        $endDate = $startDate->copy()->addMinutes($appointmentSetting->time_for_visit);
        $user = User::where('mobile', 'LIKE', "%{$mobile}%")->first();
        if (!isset($user)) {
            $user  = User::create([
                'mobile' => $mobile,
                'password' => uniqId(),
            ]);
        }

        $someoneModel = null;
        $foHimself = 1;
        // main user data
        $mainUser = new UserModel(
            user: $user,
            firstName: $user->first_name,
            lastName: $user->last_name,
            nationalCode: $nationalCode,
        );
        // full user model
        $userModelAppointment = new UserModelAppointment(userModel: $mainUser, forHimself: $foHimself, userSomeoneModel: $someoneModel, needToUpdate: true);

        //check if operator
        if (isset($operatorId) && !empty($operatorId)) {
            $oprator = null;
            $operatorExists = User::find($operatorId);
            if ($operatorExists) {
                // check if oprator exixts
                $oprator =  $operatorId;
            }
        } else {
            $oprator = null;
        }
        if (isset($kindParameter) && ! empty($kindParameter)) {
            $kind = AppointmentUserKindEnum::tryFrom((int) $kindParameter);
        } else {
            $kind = AppointmentUserKindEnum::IN_PERSION;
        }
        $kind ??= AppointmentUserKindEnum::IN_PERSION;

        $serviceId = $servicesId ?? $appointmentSetting->service_id ?? $doctor->activeServices()->first()?->id;
        $placeId = $placesId ?? $appointmentSetting->place_id ?? $doctor->activePlaces()->first()?->id;

        // appointment model
        $appointmentModel = new AppointmentModel(
            timestamp: $startDate->copy()->timestamp,
            appointmentVia: AppointmentVia::VOIP,
            sendSmsToUser: true,
            serviceId: $serviceId,
            placeId: $placeId,
            agentId: $user->id,
            operatorId: $oprator,
            kind: $kind,
            smsToDoctor: false,
            description: $description ??  '',
            type: AppointmentUserTypeEnum::MAIN__APPOINTMENT,
            endTime: $endDate->toTimeString(),
        );

        $detail = [];

        // sms Template
        if (
            data_get($appointmentSetting->detail, AppointmentSetting::PAYMENT . '.' . AppointmentSetting::STATUS) == true &&
            data_get($appointmentSetting->detail, AppointmentSetting::PAYMENT . '.' . AppointmentSetting::NOT_PAYING_STATUS) == 'dontSubmit'
        ) {
            // if payment was active
            $detail['smsTemplate']      = setting(SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT);
        } else {
            $detail['smsTemplate']      = setting(SettingKeyEnum::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL);
        }

        $storeAppointment = app('AppointmentUserService')->storeAppointment($appointmentSetting, $userModelAppointment, $appointmentModel, $detail);
        if ($storeAppointment['status']) {
            $appointmentUser = AppointmentUser::find(data_get($storeAppointment, 'detail.appointment_user_id'));
            $paymentIsActive = (bool) data_get($appointmentUser?->details, AppointmentUser::DETAIL_PAYMENT . '.status', false);
            $paymentPrice = data_get($appointmentUser?->details, AppointmentUser::DETAIL_PAYMENT . '.' . AppointmentUser::DETAIL_PAYMENT_PRICE . '.int', 0);

            // generate cache
            $appointmentSetting->runGenerateCacheJob($startDate->toDateTimeString());
            return $this->ok(
                [
                    'status' => true,
                    'message' => 'نوبت با موفقیت ذخیره شد',
                    'active_online_payment' => $paymentIsActive,
                    'price' => $paymentPrice,
                    'active_payment_by_phone' => false,
                    'appointmentCode' => data_get($storeAppointment, 'detail.appointment_user_id'),
                    'tracking_code' => data_get($storeAppointment, 'detail.tracking_code'),
                    'payment_link' => data_get($storeAppointment, 'detail.payment_link'),
                ]
            );
        } else {
            return $this->requestException([
                'status' => false,
                'message' => 'خطا در ثبت نوبت.'
            ]);
        }
    }
}
