<?php

namespace Modules\Api\Http\Controllers\Tenant;

use App\Enum\ActiveEnum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Modules\AppointmentSetting\app\Enum\AppintmentSettingDayNumber;
use Modules\AppointmentSetting\app\Models\AppointmentSegmentItem;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\Api\Entities\AuthRequest;
use Modules\Api\Trait\ApiHandlerTrait;
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentUser\app\Models\AppointmentOnlineMessage;
use Modules\AppointmentUser\app\Models\AppointmentOnlineMessageFile;
use Modules\AppointmentUser\app\Models\AppointmentUser;
use Modules\AppointmentUser\app\Notifications\AppointmentSmsNotification;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageSeenEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentVia;
use Modules\AppointmentUser\Enum\AppointmentUserKindEnum;
use Modules\AppointmentUser\Enum\AppointmentUserStatusEnum;
use Modules\AppointmentUser\Enum\AppointmentUserTypeEnum;
use Modules\AppointmentUser\Enum\model\AppointmentModel;
use Modules\AppointmentUser\Enum\model\UserModel;
use Modules\AppointmentUser\Enum\model\UserModelAppointment;
use Modules\Front\app\Models\Comment;
use Modules\Front\app\Models\Contactus;
use Modules\Front\app\Models\Faq;
use Modules\Front\app\Models\Province;
use Modules\Front\enum\CommentShowHomePage;
use Modules\Front\enum\CommentStatusEnum;
use Modules\Place\app\Models\Place;
use Modules\Service\app\Models\Service;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Enum\AppointmentModeEnum;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\Speciality\app\Models\Speciality;
use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;

/**
 * API مخصوص اپ جدید (React).
 * فقط زمانی در دسترس است که قالب جدید در تنظیمات فعال باشد.
 */
class TenantAppController extends Controller
{
    use ApiHandlerTrait;

    /** A valid bcrypt hash used to reduce timing differences for unknown admin accounts. */
    private const DUMMY_ADMIN_PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

    /**
     * fileinfo در بعضی سرورها WebM صوتی مرورگر را به صورت video/webm تشخیص می دهد.
     */
    private const CHAT_AUDIO_FILE_MIMES = [
        'audio/webm', 'video/webm', 'audio/ogg', 'audio/mpeg', 'audio/mp4', 'audio/wav', 'audio/x-wav',
    ];

    /**
     * نوع فایل های مجاز برای پیوست گفتگوی آنلاین: عکس، سند رایج و پیام صوتی.
     * عمداً از فرمت های اجرایی/اسکریپتی (exe, php, js, sh, ...) خالی است.
     */
    private const CHAT_ALLOWED_FILE_MIMES = [
        // عکس
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/heic', 'image/heif',
        // سند
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        // پیام صوتی ضبط شده در مرورگر
        ...self::CHAT_AUDIO_FILE_MIMES,
    ];

    /**
     * داده های اولیه اپ: تنظیمات سایت، حالت نوبت دهی، تصاویر و کاربر فعلی.
     */
    public function bootstrap(): \Illuminate\Http\JsonResponse
    {
        return $this->ok([
            'status' => true,
            'mode' => AppointmentModeEnum::current()->value,
            'site' => $this->siteInfo(),
            'images' => $this->templateImages(),
            'user' => $this->currentUser(),
        ]);
    }

    /**
     * داده صفحه اصلی. بر اساس حالت نوبت دهی، بخش های لازم را برمیگرداند.
     */
    public function home(Request $request): \Illuminate\Http\JsonResponse
    {
        $mode = AppointmentModeEnum::current();

        $payload = [
            'status' => true,
            'mode' => $mode->value,
            'about_title' => setting(SettingKeyEnum::SITE_SECEND_SECTION_TITLE),
            'about' => setting(SettingKeyEnum::SITE_SECEND_SECTION_DESCRIPTION),
            'comments' => $this->comments(),
            'hero' => $this->heroContent(),
        ];

        if ($mode === AppointmentModeEnum::CLINIC) {
            $provinceId = $request->get('province');
            $doctors = $provinceId
                ? $this->listedDoctors()->filter(fn (User $u) => $this->doctorInProvince($u, (int) $provinceId))->values()
                : $this->listedDoctors();

            $rows = $this->clinicDoctorRows($doctors);

            $payload['doctors'] = $rows;
            $payload['specialities'] = $this->clinicSpecialities($doctors);
            $payload['departments'] = $this->clinicDepartments($doctors);
            $payload['services'] = $this->homeServices($doctors);
            $payload['provinces'] = $this->provinceList();
            $payload['stats'] = $this->clinicStats($rows, $payload['departments']);

            return $this->ok($payload);
        }

        // حالت های تک پزشک: پزشک اصلی مطب
        $owner = $this->primaryDoctor();

        $payload['doctor'] = $owner ? $this->doctorPayload($owner) : null;
        $payload['hero'] = $this->heroContent($owner);
        $payload['services'] = $this->homeServices($this->listedDoctors());
        $payload['places'] = $owner ? $this->places($owner, $mode === AppointmentModeEnum::SINGLE_DOCTOR) : [];
        $payload['stats'] = $owner ? $this->doctorStats($owner) : [];
        $payload['timeline'] = $this->doctorTimeline();
        $payload['working_hours'] = $owner ? $this->workingHours($owner, null, $mode === AppointmentModeEnum::SINGLE_DOCTOR) : [];

        if ($mode === AppointmentModeEnum::SINGLE_DOCTOR) {
            $payload['contact_email'] = setting(SettingKeyEnum::CONTACTUS_FORM_SUPPORT_EMAIL);
            $payload['faqs'] = Faq::active()->priority()->get(['id', 'question', 'answer'])->toArray();
        }

        // اگر برای بخش «درباره» متنی در تنظیمات سایت وارد نشده، بیوگرافی پزشک اصلی استفاده میشود
        if (! $payload['about'] && $owner) {
            $payload['about_title'] = $payload['about_title'] ?? 'درباره ' . $owner->full_name;
            $payload['about'] = $owner->drBiography;
        }

        if ($mode === AppointmentModeEnum::SINGLE_DOCTOR_WITH_DOCTORS) {
            // سایر اعضای تیم، به جز پزشک اصلی
            $team = $this->listedDoctors()
                ->reject(fn (User $u) => $owner && $u->id === $owner->id);

            $payload['doctors'] = $this->doctorList($team);
            $payload['devices_description'] = setting(SettingKeyEnum::NEW_TPL_DEVICES_DESCRIPTION)
                ?: setting(SettingKeyEnum::APPOINTMENT_GALLERY_BODY);
        }

        return $this->ok($payload);
    }

    /**
     * داده صفحه «درباره ما».
     */
    public function aboutUs(): \Illuminate\Http\JsonResponse
    {
        $settingsModel = new Setting();
        $settings = Setting::getSettingByArray([
            SettingKeyEnum::ABOUT_US_FIRST_SECTION_TITLE,
            SettingKeyEnum::ABOUT_US_FIRST_SECTION_DESCRIPTION,
            SettingKeyEnum::ABOUT_US_SECEND_SECTION_TITLE,
            SettingKeyEnum::ABOUT_US_SECEND_SECTION_DESCRIPTION,
            SettingKeyEnum::ABOUT_US_SECEND_SECTION_IMAGE,
            SettingKeyEnum::ABOUT_US_THIRD_SECTION_TITLE,
            SettingKeyEnum::ABOUT_US_THIRD_SECTION_DESCRIPTION,
            SettingKeyEnum::ABOUT_US_THIRD_SECTION_IMAGE,
            SettingKeyEnum::ABOUT_US_FOURTH_SECTION_TITLE,
            SettingKeyEnum::ABOUT_US_FOURTH_SECTION_DESCRIPTION,
            SettingKeyEnum::ABOUT_US_FOURTH_SECTION_IMAGE,
        ]);

        $imageUrl = fn ($key) => ($path = $settingsModel->vc($settings, $key))
            ? Storage::url($path)
            : null;

        return $this->ok([
            'status' => true,
            'first_title' => $settingsModel->vc($settings, SettingKeyEnum::ABOUT_US_FIRST_SECTION_TITLE),
            'first_body' => $settingsModel->vc($settings, SettingKeyEnum::ABOUT_US_FIRST_SECTION_DESCRIPTION),
            'second_title' => $settingsModel->vc($settings, SettingKeyEnum::ABOUT_US_SECEND_SECTION_TITLE),
            'second_body' => $settingsModel->vc($settings, SettingKeyEnum::ABOUT_US_SECEND_SECTION_DESCRIPTION),
            'second_image' => $imageUrl(SettingKeyEnum::ABOUT_US_SECEND_SECTION_IMAGE),
            'third_title' => $settingsModel->vc($settings, SettingKeyEnum::ABOUT_US_THIRD_SECTION_TITLE),
            'third_body' => $settingsModel->vc($settings, SettingKeyEnum::ABOUT_US_THIRD_SECTION_DESCRIPTION),
            'third_image' => $imageUrl(SettingKeyEnum::ABOUT_US_THIRD_SECTION_IMAGE),
            'fourth_title' => $settingsModel->vc($settings, SettingKeyEnum::ABOUT_US_FOURTH_SECTION_TITLE),
            'fourth_body' => $settingsModel->vc($settings, SettingKeyEnum::ABOUT_US_FOURTH_SECTION_DESCRIPTION),
            'fourth_image' => $imageUrl(SettingKeyEnum::ABOUT_US_FOURTH_SECTION_IMAGE),
            'faqs' => Faq::active()->priority()->get(['id', 'question', 'answer'])->toArray(),
        ]);
    }

    /**
     * داده صفحه «تماس با ما».
     */
    public function contactUs(): \Illuminate\Http\JsonResponse
    {
        return $this->ok([
            'status' => true,
            'first_section_show' => (bool) setting(SettingKeyEnum::CONTACTUS_FIRST_SECTION_STATUS),
            'first_section_title' => setting(SettingKeyEnum::CONTACTUS_FIRST_SECTION_TITLE),
            'first_section_body' => setting(SettingKeyEnum::CONTACTUS_FIRST_SECTION_BODY),
            'form_active' => (bool) setting(SettingKeyEnum::CONTACTUS_FORM_STATUS),
            'address' => setting(SettingKeyEnum::CONTACTUS_FORM_ADDRESS),
            'email' => setting(SettingKeyEnum::CONTACTUS_FORM_SUPPORT_EMAIL),
            'faqs' => Faq::active()->priority()->get(['id', 'question', 'answer'])->toArray(),
        ]);
    }

    /**
     * ثبت پیام کاربر در صفحه «تماس با ما».
     * معادل ContactUsLivewire::sendSupportMessage.
     */
    public function sendContactMessage(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if ($user) {
            $rules = ['message' => 'required|string|max:1500'];
        } else {
            $rules = [
                'mobile' => 'required|digits:11',
                'full_name' => 'required|string|max:225',
                'message' => 'required|string|max:1500',
            ];
        }

        $request->validate($rules);

        Contactus::create($user ? [
            'user_id' => $user->id,
            'body' => $request->get('message'),
        ] : [
            'name' => $request->get('full_name'),
            'mobile' => $request->get('mobile'),
            'body' => $request->get('message'),
        ]);

        return $this->ok([
            'status' => true,
            'message' => 'پیام شما با موفقیت ثبت شد.',
        ]);
    }

    /**
     * پزشکانی که این خدمت را ارائه می دهند.
     * وقتی مودال رزرو فقط با شناسه خدمت باز می شود (نه پزشک مشخص)،
     * اپ ابتدا این مسیر را صدا میزند تا در صورت وجود چند پزشک، انتخاب پزشک را نشان دهد.
     */
    public function serviceDoctors(Service $service): \Illuminate\Http\JsonResponse
    {
        $doctors = User::doctors_query()
            ->whereHas('metas', function ($q) {
                $q->where('meta_key', UserMetaEnum::ACTIVE_APPOINTMENT)->where('meta_value', true);
            })
            ->whereHas('services', fn ($q) => $q->where('services.id', $service->id))
            ->get()
            ->filter(fn (User $doctor) => $this->doctorIsBookable($doctor))
            ->values();

        return $this->ok([
            'status' => true,
            'service_id' => $service->id,
            'service' => [
                'id' => $service->id,
                'title' => $service->title,
                'description' => $service->description,
                'image' => $service->icon ?: null,
            ],
            'doctors' => $this->clinicDoctorRows($doctors),
        ]);
    }

    /**
     * جست و جوی پزشکان.
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = trim((string) $request->get('query', ''));
        $speciality = trim((string) $request->get('speciality', ''));
        $serviceId = $request->get('service_id');
        $provinceId = $request->get('province');
        $sort = (string) $request->get('sort', 'soonest');

        $builder = User::doctors_query()
            ->whereHas('metas', function ($q) {
                $q->where('meta_key', UserMetaEnum::ACTIVE_APPOINTMENT)->where('meta_value', true);
            });

        if ($speciality !== '') {
            $builder->whereHas('specialities', fn ($q) => $q->where('title', 'LIKE', "%{$speciality}%"));
        }

        if ($serviceId) {
            $builder->whereHas('services', fn ($q) => $q->where('services.id', $serviceId));
        }

        if ($provinceId) {
            $builder->whereHas('places', fn ($q) => $q->whereJsonContains('detail->' . Place::DETAIL_PROVINCE, (int) $provinceId));
        }

        if ($query !== '' && $query !== 'پزشکان') {
            $builder->where(function ($outer) use ($query) {
                $outer->whereHas('metas', function ($q) use ($query) {
                    $q->whereIn('meta_key', [UserMetaEnum::FIRST_NAME, UserMetaEnum::LAST_NAME])
                        ->where('meta_value', 'LIKE', "%{$query}%");
                })->orWhereHas('specialities', fn ($q) => $q->where('title', 'LIKE', "%{$query}%"));
            });
        }

        $doctors = $builder->get()->filter(
            fn (User $doc) => $doc->services()->exists() && $doc->places()->exists()
        );

        $rows = collect($this->doctorList($doctors))->map(function (array $row) {
            $row['locations'] = [];

            return $row;
        });

        $rows = match ($sort) {
            'rating' => $rows->sortByDesc('rating')->values(),
            'name' => $rows->sortBy('name')->values(),
            default => $rows->values(),
        };

        return $this->ok([
            'status' => true,
            'doctors' => $rows,
            'specialities' => $this->specialities(),
            'provinces' => $this->provinceList(),
        ]);
    }

    /**
     * پروفایل کامل یک پزشک.
     */
    public function doctor(User $doctor): \Illuminate\Http\JsonResponse
    {
        if (! $doctor->IsDoctor()) {
            return $this->notFound();
        }

        // پنل نوبت دهی در خود صفحه پروفایل است، پس روزها را همینجا می فرستیم
        $setting = $this->resolveAppointmentSetting($doctor, null);
        $days = [];
        if ($setting) {
            try {
                $days = $this->buildDays(
                    app('AppointmentUserService')->listAppointments($setting),
                    $setting,
                );
            } catch (\Throwable) {
                $days = [];
            }
        }

        return $this->ok([
            'status' => true,
            'doctor' => array_merge($this->doctorPayload($doctor), $this->doctorStats($doctor), [
                'gallery' => $this->gallery($doctor),
                'education' => [],
            ]),
            'services' => $this->services($doctor),
            'places' => $this->places($doctor),
            'reviews' => $this->comments($doctor->id),
            'booking' => [
                'appointment_setting_id' => $setting?->id,
                'doctor_id' => $doctor->id,
                'place_id' => $setting?->place_id
                    ?? $doctor->places()->active()->orderBy('priority')->value('places.id'),
                'service_id' => $setting?->service_id
                    ?? $doctor->services()->active()->orderBy('priority')->value('services.id'),
                'days' => $days,
            ],
            'related' => $this->relatedDoctors($doctor),
        ]);
    }

    /**
     * پزشکان مشابه، برای بخش پایانی صفحه پروفایل.
     */
    private function relatedDoctors(User $doctor, int $limit = 3): array
    {
        $specialityIds = $doctor->specialities->pluck('id');

        $query = User::doctors_query()
            ->where('users.id', '!=', $doctor->id)
            ->whereHas('metas', function ($q) {
                $q->where('meta_key', UserMetaEnum::ACTIVE_APPOINTMENT)->where('meta_value', true);
            });

        if ($specialityIds->isNotEmpty()) {
            $query->whereHas('specialities', fn ($q) => $q->whereIn('specialities.id', $specialityIds));
        }

        $related = $query->limit($limit)->get();

        // اگر هم تخصصی پیدا نشد، چند پزشک فعال دیگر نمایش داده میشود
        if ($related->isEmpty()) {
            $related = User::doctors_query()
                ->where('users.id', '!=', $doctor->id)
                ->whereHas('metas', function ($q) {
                    $q->where('meta_key', UserMetaEnum::ACTIVE_APPOINTMENT)->where('meta_value', true);
                })
                ->limit($limit)
                ->get();
        }

        return $this->doctorList($related);
    }

    /**
     * روزها و ساعت های آزاد یک پزشک، برای مودال دریافت نوبت.
     *
     * منطق تولید ساعت ها همان سرویس موجود سیستم است تا رفتار نوبت دهی در اپ
     * جدید دقیقاً با نسخه فعلی یکی باشد.
     */
    public function doctorDays(User $doctor, Request $request): \Illuminate\Http\JsonResponse
    {
        $serviceId = $request->get('service_id');
        $placeId = $request->get('place_id');

        // اگر مطب هم مشخص باشد، تنظیمات دقیق همان مطب و بخش پیدا میشود؛
        // در نبود آن، findSettingId خودش به تنظیمات عمومی پزشک برمیگردد.
        $setting = ($serviceId && $placeId)
            ? AppointmentSetting::findSettingId($doctor->id, $serviceId, $placeId)
            : $this->resolveAppointmentSetting($doctor, $serviceId);

        if (! $setting) {
            return $this->ok([
                'status' => true,
                'days' => [],
                'message' => 'برای این پزشک تنظیمات نوبت دهی ثبت نشده است.',
            ]);
        }

        // پیغام غیرفعال بودن موقت نوبت دهی
        $deactivation = $setting->detail[AppointmentSetting::TEMPORARY_DEACTIVATION_ONLINE] ?? null;
        if (is_array($deactivation) && ($deactivation[AppointmentSetting::STATUS] ?? false)) {
            return $this->ok([
                'status' => true,
                'days' => [],
                'message' => $deactivation[AppointmentSetting::TEMPORARY_DEACTIVATION_ONLINE_MESSAGE]
                    ?? 'هم اکنون امکان دریافت نوبت فراهم نیست.',
            ]);
        }

        try {
            // مدت ویزیت با جمع زمان ناحیه های انتخاب شده تعیین میشود،
            // مطابق ShowAvailableDayForDoctor.
            $details = [];
            $segmentTime = $this->segmentTime($request->get('segment_ids', []));
            if ($segmentTime !== null) {
                $details['segment_time'] = $segmentTime;
            }

            $list = app('AppointmentUserService')->listAppointments($setting, $details);
        } catch (\Throwable) {
            return $this->ok([
                'status' => true,
                'days' => [],
                'message' => 'در حال حاضر امکان نمایش زمان ها وجود ندارد.',
            ]);
        }

        // صفحه تایید نوبت به شناسه محل و خدمت نیاز دارد؛ اگر در تنظیمات مشخص
        // نشده باشند، اولین مورد فعال پزشک استفاده میشود.
        $placeId = $setting->place_id ?? $doctor->places()->active()->orderBy('priority')->value('places.id');
        $resolvedServiceId = $setting->service_id
            ?? $serviceId
            ?? $doctor->services()->active()->orderBy('priority')->value('services.id');

        return $this->ok([
            'status' => true,
            'appointment_setting_id' => $setting->id,
            'doctor_id' => $doctor->id,
            // برای متن تاییدیه در مودال انتخاب ساعت
            'doctor_name' => $doctor->full_name,
            'place_id' => $placeId,
            'service_id' => $resolvedServiceId,
            'service_title' => $resolvedServiceId ? Service::find($resolvedServiceId)?->title : null,
            'days' => $this->buildDays($list, $setting),
        ]);
    }

    /**
     * تنظیمات نوبت دهی مناسب برای پزشک و خدمت انتخاب شده.
     * اگر برای آن خدمت تنظیمات اختصاصی نباشد، تنظیمات عمومی پزشک استفاده میشود.
     */
    /**
     * آیا اصلاً میتوان از این پزشک نوبت گرفت.
     * معادل isDocAvailable در DoctorProfileLivewire.
     */
    private function doctorIsBookable(User $doctor): bool
    {
        if (! filter_var(setting(SettingKeyEnum::APPOINTMENT_STATUS), FILTER_VALIDATE_BOOL)) {
            return false;
        }

        if (! $doctor->isDoctorActive()) {
            return false;
        }

        return AppointmentSetting::activeSetting()->where('user_id', $doctor->id)->exists();
    }

    /**
     * مطب های فعال پزشک؛ در صورت مشخص بودن خدمت، فقط مطب هایی که آن خدمت را دارند.
     * معادل availablePlaces در DoctorProfileLivewire.
     */
    private function availablePlaces(User $doctor, $serviceId = null)
    {
        $places = $doctor->places()->where('places.active', ActiveEnum::ACTIVE->value);

        $serviceHasPlace = $serviceId && Place::whereHas('service', function ($q) use ($serviceId) {
            $q->where('services.id', $serviceId);
        })->exists();

        if ($serviceHasPlace) {
            $places->whereHas('service', function ($q) use ($serviceId) {
                $q->where('services.id', $serviceId);
            });
        }

        return $places->get();
    }

    /**
     * خدمت های فعال پزشک، بدون خدمت هایی که تنظیمات نوبت دهی آنها غیرفعال شده.
     * دقیقاً معادل availableServices در DoctorProfileLivewire.
     *
     * توجه: اینجا عمداً بر اساس مطب فیلتر نمیشود، چون جدول واسط مطب و خدمت
     * در عمل پر نشده است و فیلتر کردن باعث میشود بیشتر خدمت های پزشک
     * از فهرست حذف شوند. رفتار صفحه پروفایل پزشک هم همین است.
     */
    private function availableServices(User $doctor)
    {
        return $doctor->services()
            ->where('services.active', ActiveEnum::ACTIVE->value)
            ->whereNotExists(function ($q) use ($doctor) {
                $q->selectRaw('1')
                    ->from('appointment_settings')
                    ->where('appointment_settings.user_id', $doctor->id)
                    ->whereColumn('appointment_settings.service_id', 'services.id')
                    ->where('appointment_settings.active', ActiveEnum::DEACTIVE->value)
                    ->whereNull('appointment_settings.deleted_at');
            })
            ->get();
    }
    /**
     * جمع زمان ناحیه های انتخاب شده؛ اگر ناحیه ای انتخاب نشده باشد null.
     * معادل محاسبه segment_time در ShowAvailableDayForDoctor.
     */
    private function segmentTime($segmentIds): ?int
    {
        $ids = array_values(array_filter((array) $segmentIds, fn ($id) => is_numeric($id)));

        if ($ids === []) {
            return null;
        }

        $time = (int) AppointmentSegmentItem::whereIn('id', $ids)->sum('time');

        return $time > 0 ? $time : null;
    }
    /**
     * آیا این حساب متعلق به کارکنان است.
     * بیشتر بیماران قدیمی هیچ نقشی ندارند، بنابراین نبودِ نقشِ «بیمار»
     * دلیل رد کردن نیست؛ فقط حساب های کارکنان از این مسیر کنار گذاشته میشوند.
     */
    private function isStaffAccount(User $user): bool
    {
        // نام نقش ها دقیقاً مطابق جدول نقش های همین سامانه است
        return $user->hasAnyRole(['مدیر', 'پزشک', 'اپراتور', 'منشی', 'ماما', 'doctor']);
    }
    /**
     * اطلاعات کاربر جاری، همان شکلی که اپ برای هدر و پروفایل لازم دارد.
     */
    private function currentUserPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->full_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'mobile' => $user->mobile,
            'national_code' => $user->national_code,
            'birthday' => $this->birthdayLabel($user),
        ];
    }
    private function resolveAppointmentSetting(User $doctor, $serviceId): ?AppointmentSetting
    {
        $base = fn () => AppointmentSetting::where('user_id', $doctor->id)
            ->where('active', ActiveEnum::ACTIVE->value);

        if ($serviceId) {
            $forService = $base()->where('service_id', $serviceId)->first();
            if ($forService) {
                return $forService;
            }
        }

        return $base()->whereNull('service_id')->first() ?? $base()->first();
    }

    /**
     * تبدیل خروجی سرویس نوبت دهی به ساختار ساده مورد نیاز اپ:
     * فهرستی از روزها که هرکدام ساعت های خود را دارند.
     */
    private function buildDays(array $list, AppointmentSetting $setting): array
    {
        $maxDay = $setting->max_day_active ?? 15;
        $minDayActive = $setting->min_day_active;
        $displayLimit = $setting->emptyAppointmentDisplayLimit();

        // ساعت های پر شده فقط در صورتی نمایش داده می شوند که در تنظیمات فعال باشد
        $showBooked = filter_var(
            setting(SettingKeyEnum::SHOW_FALSE_APPOINTMENT_STATUS),
            FILTER_VALIDATE_BOOL
        );

        $days = [];

        foreach (($list['data'] ?? []) as $months) {
            foreach ($months as $daysOfMonth) {
                foreach ($daysOfMonth as $appointment) {
                    if (($appointment['empty_appoints'] ?? 0) <= 0 || ! ($appointment['status'] ?? false)) {
                        continue;
                    }

                    $gmt = $appointment['day_number_gmt'] ?? null;
                    if (! $gmt) {
                        continue;
                    }

                    // بیشتر از بازه مجاز جلوتر نرویم
                    if (Carbon::now()->addDays($maxDay)->lte(Carbon::parse($gmt))) {
                        break 3;
                    }

                    // روزهایی که هنوز به حداقل فاصله مجاز نرسیده اند
                    if (Carbon::parse($gmt)->setTime(23, 59, 59)->subDays($minDayActive)->isPast()) {
                        continue;
                    }

                    $times = [];
                    $shown = 0;

                    foreach (($appointment['times'] ?? []) as $time) {
                        $free = (bool) ($time['status'] ?? false);

                        if (! $free && ! $showBooked) {
                            continue;
                        }

                        if ($free && $displayLimit !== null && $shown >= $displayLimit) {
                            continue;
                        }

                        $times[] = [
                            // شناسه یکتا برای انتخاب در سمت اپ؛ ساعت های پر قابل انتخاب نیستند
                            'id' => $free
                                ? ($time['timestamp'] ?? $gmt . '_' . ($time['from'] ?? ''))
                                : $gmt . '_booked_' . count($times),
                            'label' => substr((string) ($time['from'] ?? ''), 0, -3),
                            'until' => substr((string) ($time['until'] ?? ''), 0, -3),
                            'free' => $free,
                            // پارامترهای لازم برای صفحه تایید نوبت
                            'start_time' => $free ? ($time['timestamp'] ?? null) : null,
                            'end_time' => $free ? substr((string) ($time['until'] ?? ''), 0, -3) : null,
                        ];

                        if ($free) {
                            $shown++;
                        }
                    }

                    if ($times === []) {
                        continue;
                    }

                    $days[] = [
                        'key' => $gmt,
                        'date' => $gmt,
                        'label' => $appointment['day_number'] ?? $gmt,
                        'free_count' => $appointment['empty_appoints'] ?? 0,
                        'times' => $times,
                    ];
                }
            }
        }

        return $days;
    }

    /**
     * اطلاعات کاربر و نوبت های او.
     */
    public function profile(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return $this->badRequest(['message' => 'ابتدا وارد حساب خود شوید.']);
        }

        $appointments = AppointmentUser::with(['doctor', 'service', 'place'])
            ->where('user_id', $user->id)
            ->orderByDesc('date_visit')
            ->limit(100)
            ->get()
            ->map(fn ($a) => $this->appointmentRow($a))
            ->values();

        return $this->ok([
            'status' => true,
            'user' => $this->currentUser(),
            // اطلاعات کامل تر برای بخش «اطلاعات من» و فرم ویرایش
            'info' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'mobile' => $user->mobile,
                'national_code' => $user->national_code,
                'birthday' => $this->birthdayLabel($user),
                'gender' => $user->gender,
                'city' => $user->city,
            ],
            'appointments' => $appointments,
        ]);
    }

    /**
     * تبدیل یک نوبت به سطر مورد نیاز صفحه پروفایل.
     * گروه بندی: پیش رو، انجام شده و لغو شده.
     */
    private function appointmentRow(AppointmentUser $a): array
    {
        $visit = $a->date_visit ? Carbon::parse($a->date_visit) : null;
        $status = $a->status;

        $cancelled = in_array($status, [
            AppointmentUserStatusEnum::STATUS_CANCEL,
            AppointmentUserStatusEnum::STATUS_DISAPPROVED,
        ], true);

        if ($cancelled) {
            $group = 'canceled';
        } elseif ($visit && $visit->isFuture()) {
            $group = 'upcoming';
        } else {
            $group = 'past';
        }

        $waitingPayment = $status === AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT;
        $price = $a->details['payment']['price'] ?? null;
        $paid = (bool) ($a->details['payment']['status'] ?? false);

        // وضعیت پرداخت برای نشان رنگی کارت
        if ($cancelled) {
            $pay = $paid ? 'refunded' : 'canceled';
        } elseif ($waitingPayment) {
            $pay = 'pending';
        } elseif ($status === AppointmentUserStatusEnum::STATUS_MONITORING) {
            $pay = 'awaiting_confirmation';
        } else {
            $pay = 'paid';
        }

        $jalali = $visit ? verta($visit) : null;

        return [
            'id' => $a->id,
            'code' => $a->tracking_code,
            'group' => $group,
            'month' => $jalali?->format('%B'),
            'day' => $jalali?->format('d'),
            'date' => $jalali?->format('l j F Y'),
            'time' => $a->start_time ? substr((string) $a->start_time, 0, 5) : null,
            'doctor' => $a->doctor?->full_name,
            'service' => $a->service?->title,
            'place' => $a->place?->title,
            'pay' => $pay,
            'status_label' => $status?->getName(),
            'fee_label' => isset($price['string'])
                ? $price['string'].' '.($price['currency'] ?? '')
                : null,
            'can_pay' => $waitingPayment,
            'can_cancel' => ! $cancelled && $visit?->isFuture() && in_array($status, [
                AppointmentUserStatusEnum::STATUS_PENDING,
                AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
                AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT,
                AppointmentUserStatusEnum::STATUS_MONITORING,
            ], true),
            'payment_url' => $waitingPayment
                ? route('api.appointment.payment.create', ['appointmentUser' => $a->id])
                : null,
        ];
    }

    /**
     * تاریخ تولد در متا به شکل JSON ذخیره میشود؛ اینجا خوانا میشود.
     */
    private function birthdayLabel(User $user): ?string
    {
        $raw = $user->birthday;

        if (empty($raw)) {
            return null;
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }

        if (is_array($raw)) {
            $parts = array_filter([
                $raw['year'] ?? null,
                $raw['month'] ?? null,
                $raw['day'] ?? null,
            ]);

            return $parts ? implode('/', $parts) : null;
        }

        return (string) $raw;
    }

    /**
     * ثبت نوبت پس از تایید بیمار در مودال انتخاب ساعت.
     * همان قوانین صفحه تایید نوبت (Checkout) رعایت میشود: بررسی مالکیت
     * محل و خدمت، گذشته نبودن زمان، و محدودیت یک نوبت در روز.
     */
    public function storeAppointment(User $doctor, Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return $this->requestException([
                'status' => false,
                'message' => 'برای ثبت نوبت ابتدا وارد حساب کاربری خود شوید.',
                'need_login' => true,
            ]);
        }

        // کاربری که هنوز ثبت نام او کامل نشده باید ابتدا اطلاعاتش را تکمیل کند
        if (trim((string) $user->first_name) === '' || trim((string) $user->last_name) === ''
            || (filter_var(setting(SettingKeyEnum::USER_REGISTER_NATIONAL_CODE_REQUIRED), FILTER_VALIDATE_BOOL)
                && trim((string) $user->national_code) === '')
            || (filter_var(setting(SettingKeyEnum::USER_REGISTER_BIRTHDAY_REQUIRED), FILTER_VALIDATE_BOOL)
                && ! $user->birthday)) {
            return $this->requestException([
                'status' => false,
                'message' => 'برای ثبت نوبت ابتدا ثبت نام خود را کامل کنید.',
                'need_registration' => true,
                'user' => $this->currentUserPayload($user),
            ]);
        }

        $serviceId = $request->get('service_id');
        $requestedPlaceId = $request->get('place_id');
        $visitType = $request->get('visit_type') ?: 'in_person';

        // همان تنظیماتی که هنگام نمایش ساعت ها استفاده شد
        $setting = ($serviceId && $requestedPlaceId)
            ? AppointmentSetting::findSettingId($doctor->id, $serviceId, $requestedPlaceId)
            : $this->resolveAppointmentSetting($doctor, $serviceId);

        if (! $setting) {
            return $this->badRequest([
                'status' => false,
                'message' => 'برای این پزشک تنظیمات نوبت دهی ثبت نشده است.',
            ]);
        }

        if ($visitType === 'online') {
            return $this->storeOnlineAppointment($doctor, $user, $setting, $serviceId, $requestedPlaceId);
        }

        $startTime = $request->get('start_time');

        if (empty($startTime) || ! is_numeric($startTime)) {
            return $this->badRequest([
                'status' => false,
                'message' => 'زمان انتخابی معتبر نیست، لطفا دوباره یک ساعت را انتخاب کنید.',
            ]);
        }

        $startsAt = Carbon::createFromTimestamp((int) $startTime, 'Asia/Tehran');

        if ($startsAt->isPast()) {
            return $this->badRequest([
                'status' => false,
                'message' => 'زمان انتخابی گذشته است، لطفا یک ساعت دیگر انتخاب کنید.',
            ]);
        }

        // انتخاب کاربر مقدم است؛ سپس تنظیمات و در آخر اولین مورد فعال پزشک
        $placeId = $requestedPlaceId
            ?: ($setting->place_id
                ?? $doctor->places()->active()->orderBy('priority')->value('places.id'));
        $resolvedServiceId = $serviceId
            ?: ($setting->service_id
                ?? $doctor->services()->active()->orderBy('priority')->value('services.id'));

        if (! $placeId || ! $resolvedServiceId) {
            return $this->badRequest([
                'status' => false,
                'message' => 'اطلاعات محل یا خدمت این پزشک کامل نیست.',
            ]);
        }

        // محدودیت یک نوبت فعال در هر روز، مطابق تنظیمات سایت
        if (! filter_var(setting(SettingKeyEnum::APPOINTMENT_MORE_THAT_ONE_PER_DAY), FILTER_VALIDATE_BOOL)) {
            $hasActive = $user->appointments()
                ->whereDate('date_visit', $startsAt->toDateString())
                ->where('kind', AppointmentUserKindEnum::IN_PERSION)
                ->whereIn('status', [
                    AppointmentUserStatusEnum::STATUS_PENDING,
                    AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
                    AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT,
                    AppointmentUserStatusEnum::STATUS_ATTENDED,
                    AppointmentUserStatusEnum::STATUS_NOT_ATTENDED,
                    AppointmentUserStatusEnum::STATUS_MONITORING,
                    AppointmentUserStatusEnum::STATUS_ONILNE_CLOSED,
                ])->exists();

            if ($hasActive) {
                return $this->badRequest([
                    'status' => false,
                    'message' => 'شما یک نوبت فعال در این روز دارید!',
                ]);
            }
        }

        $segmentIds = array_values(array_filter(
            (array) $request->get('segment_ids', []),
            fn ($id) => is_numeric($id),
        ));

        // مدت ویزیت: جمع زمان ناحیه های انتخاب شده، وگرنه مدت پیش فرض تنظیمات
        $visitMinutes = $this->segmentTime($segmentIds) ?? $setting->time_for_visit;
        $appointmentModel = new AppointmentModel(
            timestamp: (int) $startTime,
            appointmentVia: AppointmentVia::SELF,
            sendSmsToUser: true,
            serviceId: $resolvedServiceId,
            placeId: $placeId,
            agentId: $user->id,
            operatorId: null,
            kind: AppointmentUserKindEnum::IN_PERSION,
            smsToDoctor: ! ($setting->doctor->drStoreAppSms ?? false),
            description: '',
            type: AppointmentUserTypeEnum::MAIN__APPOINTMENT,
            endTime: $startsAt->copy()->addMinutes($visitMinutes)->toTimeString(),
        );

        $userModelAppointment = new UserModelAppointment(
            userModel: new UserModel(
                user: $user,
                firstName: $user->firstName,
                lastName: $user->lastName,
            ),
            forHimself: 1,
            userSomeoneModel: null,
        );


        $paymentDetail = $setting->detail[AppointmentSetting::PAYMENT] ?? [];
        $detail = [
            'smsTemplate' => (($paymentDetail[AppointmentSetting::STATUS] ?? false)
                && ($paymentDetail[AppointmentSetting::NOT_PAYING_STATUS] ?? null) === 'dontSubmit')
                ? setting(SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT)
                : setting(SettingKeyEnum::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL),
        ];

        if ($segmentIds !== []) {
            // سرویس این مقدار را با explode میخواند، پس رشته جدا شده با کاما لازم است
            $detail['segments_ids'] = implode(',', array_map('intval', $segmentIds));
        }

        try {
            $result = app('AppointmentUserService')
                ->storeAppointment($setting, $userModelAppointment, $appointmentModel, $detail);
        } catch (\Throwable $e) {
            report($e);

            return $this->badRequest([
                'status' => false,
                'message' => 'در ثبت نوبت خطایی رخ داد، لطفا دوباره تلاش کنید.',
            ]);
        }

        if (! ($result['status'] ?? false)) {
            return $this->badRequest([
                'status' => false,
                // پیام سرویس معمولاً دلیل دقیق را میگوید (مثلاً پر شدن ساعت)
                'message' => $result['message'] ?? 'ثبت نوبت انجام نشد.',
                // اگر ساعت پر شده باشد، اپ باید فهرست ساعت ها را تازه کند
                'refresh_times' => ($result['route'] ?? null) === 'time',
            ]);
        }

        $trackingCode = $result['detail']['tracking_code'] ?? null;

        return $this->created([
            'status' => true,
            'message' => $result['message'] ?? 'نوبت با موفقیت ثبت شد.',
            'tracking_code' => $trackingCode,
            // مسیر صفحه جزئیات نوبت در همین اپ
            'redirect' => $trackingCode ? '/appointment/'.$trackingCode : null,
        ]);
    }

    /**
     * ثبت نوبت آنلاین (گفت و گو محور، بدون ساعت مشخص).
     * معادل OnlineAppointmentdescription::setOnlineApp در فلوی قدیمی: زمان
     * واقعی ندارد و فقط یک بازه ی جایگزین (فردا) برای آن ثبت میشود.
     */
    private function storeOnlineAppointment(
        User $doctor,
        $user,
        AppointmentSetting $setting,
        $serviceId,
        $requestedPlaceId
    ): \Illuminate\Http\JsonResponse {
        $placeId = $requestedPlaceId
            ?: ($setting->place_id
                ?? $doctor->places()->active()->orderBy('priority')->value('places.id'));
        $resolvedServiceId = $serviceId
            ?: ($setting->service_id
                ?? $doctor->services()->active()->orderBy('priority')->value('services.id'));

        if (! $placeId || ! $resolvedServiceId) {
            return $this->badRequest([
                'status' => false,
                'message' => 'اطلاعات محل یا خدمت این پزشک کامل نیست.',
            ]);
        }

        if (! ($setting->detail[AppointmentSetting::VISIT_TYPE_ONLINE] ?? false)) {
            return $this->badRequest([
                'status' => false,
                'message' => 'نوبت آنلاین برای این بخش فعال نیست.',
            ]);
        }

        // نوبت آنلاین زمان واقعی ندارد؛ مثل فلوی قدیمی برای فردا ثبت میشود
        $startsAt = Carbon::now('Asia/Tehran')->addDay();

        // محدودیت یک نوبت آنلاین فعال در هر روز؛ برخلاف نوبت حضوری، این محدودیت
        // مستقل از تنظیم عمومی «امکان دریافت بیش از یک نوبت در روز» همیشه اعمال میشود
        $activeAppointment = $user->appointments()
            ->whereDate('date_visit', $startsAt->toDateString())
            ->where('kind', AppointmentUserKindEnum::ONLINE)
            ->whereIn('status', [
                AppointmentUserStatusEnum::STATUS_PENDING,
                AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
                AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT,
                AppointmentUserStatusEnum::STATUS_ATTENDED,
                AppointmentUserStatusEnum::STATUS_NOT_ATTENDED,
                AppointmentUserStatusEnum::STATUS_MONITORING,
            ])->first();

        if ($activeAppointment) {
            return $this->badRequest([
                'status' => false,
                'message' => 'شما یک نوبت آنلاین فعال دارید!',
                // اگر پرداخت آن نوبت تمام شده باشد، اپ می تواند مستقیم به گفتگویش هدایت کند
                'online_chat_id' => $activeAppointment->status !== AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT
                    ? $activeAppointment->online()->value('id')
                    : null,
                'appointment_code' => $activeAppointment->tracking_code,
            ]);
        }

        // سقف تعداد نوبت آنلاین در روز، مستقل از محدودیت بالا
        $maxOnlinePerDay = $setting->detail[AppointmentSetting::MAX_ACTIVE_APP_FOR_ONLINE_APP] ?? null;

        if (is_numeric($maxOnlinePerDay)) {
            $onlineCount = AppointmentUser::activeAppointmentStatus()
                ->onlineAppointment()
                ->whereDate('date_visit', $startsAt->toDateString())
                ->count();

            if ($onlineCount >= (int) $maxOnlinePerDay) {
                return $this->badRequest([
                    'status' => false,
                    'message' => 'ظرفیت نوبت‌های آنلاین برای فردا تکمیل شده است، لطفا بعدا دوباره تلاش کنید.',
                    'refresh_times' => true,
                ]);
            }
        }

        $appointmentModel = new AppointmentModel(
            timestamp: $startsAt->timestamp,
            appointmentVia: AppointmentVia::SELF,
            sendSmsToUser: true,
            serviceId: $resolvedServiceId,
            placeId: $placeId,
            agentId: $user->id,
            operatorId: null,
            kind: AppointmentUserKindEnum::ONLINE,
            smsToDoctor: ! ($setting->doctor->drStoreAppSms ?? false),
            description: '',
            type: AppointmentUserTypeEnum::MAIN__APPOINTMENT,
            endTime: $startsAt->toTimeString(),
        );

        $userModelAppointment = new UserModelAppointment(
            userModel: new UserModel(
                user: $user,
                firstName: $user->firstName,
                lastName: $user->lastName,
            ),
            forHimself: 1,
            userSomeoneModel: null,
        );

        $paymentDetail = $setting->detail[AppointmentSetting::PAYMENT] ?? [];
        $onlinePayment = $paymentDetail[AppointmentSetting::ONLINE] ?? [];
        $detail = [
            'smsTemplate' => (($onlinePayment[AppointmentSetting::STATUS] ?? false)
                && ($paymentDetail[AppointmentSetting::NOT_PAYING_STATUS] ?? null) === 'dontSubmit')
                ? setting(SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT)
                : setting(SettingKeyEnum::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL),
        ];

        try {
            $result = app('AppointmentUserService')
                ->storeAppointment($setting, $userModelAppointment, $appointmentModel, $detail);
        } catch (\Throwable $e) {
            report($e);

            return $this->badRequest([
                'status' => false,
                'message' => 'در ثبت نوبت خطایی رخ داد، لطفا دوباره تلاش کنید.',
            ]);
        }

        if (! ($result['status'] ?? false)) {
            return $this->badRequest([
                'status' => false,
                'message' => $result['message'] ?? 'ثبت نوبت انجام نشد.',
            ]);
        }

        $trackingCode = $result['detail']['tracking_code'] ?? null;

        return $this->created([
            'status' => true,
            'message' => $result['message'] ?? 'نوبت آنلاین با موفقیت ثبت شد.',
            'tracking_code' => $trackingCode,
            'redirect' => $trackingCode ? '/appointment/'.$trackingCode : null,
        ]);
    }

    /**
     * جزئیات یک نوبت بر اساس کد پیگیری.
     * مانند صفحه قبلی، مشاهده بدون ورود ممکن است؛ لغو فقط برای صاحب نوبت.
     */
    public function appointment(string $trackingCode): \Illuminate\Http\JsonResponse
    {
        $appointment = AppointmentUser::with(['doctor', 'service', 'place', 'user'])
            ->where('tracking_code', $trackingCode)
            ->first();

        if (! $appointment) {
            return $this->notFound();
        }

        $visit = $appointment->date_visit ? Carbon::parse($appointment->date_visit) : null;
        $status = $appointment->status;

        // آدرس و مختصات و تلفن مطب داخل ستون detail محل ذخیره شده اند
        $placeDetail = $appointment->place?->detail ?? [];
        $location = $placeDetail['location'] ?? [];
        $lat = $location['location_lat'] ?? null;
        $lng = $location['location_lng'] ?? null;

        // مبلغ ویزیت در جزئیات پرداخت همان نوبت ثبت شده است
        $price = $appointment->details['payment']['price'] ?? null;

        // endpoint لغو فقط برای صاحب نوبت کار میکند، پس دکمه لغو هم فقط به او نشان داده میشود
        $isOwner = Auth::id() !== null && (int) Auth::id() === (int) $appointment->user_id;

        $canCancel = $isOwner && in_array($status, [
            AppointmentUserStatusEnum::STATUS_PENDING,
            AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
            AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT,
            AppointmentUserStatusEnum::STATUS_MONITORING,
        ], true) && $visit?->isFuture();

        $isOnline = $appointment->kind === AppointmentUserKindEnum::ONLINE;
        // برای هدایت به صفحه گفتگو؛ فقط نوبت های آنلاین این را دارند
        $onlineChatId = $isOnline ? $appointment->online()->value('id') : null;

        return $this->ok([
            'status' => true,
            'appointment' => [
                'code' => $appointment->tracking_code,
                // کلید ظاهری برای رنگ نشان وضعیت در اپ
                'status' => match ($status) {
                    AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT => 'pending',
                    AppointmentUserStatusEnum::STATUS_CANCEL,
                    AppointmentUserStatusEnum::STATUS_DISAPPROVED => 'cancelled',
                    AppointmentUserStatusEnum::STATUS_MONITORING => 'awaiting_confirmation',
                    default => 'paid',
                },
                'status_label' => $status?->getName(),
                'doctor' => $appointment->doctor?->full_name,
                'specialty' => $appointment->doctor?->specialities()->value('title'),
                'service' => $appointment->service?->title,
                'place' => $appointment->place?->title,
                'address' => $placeDetail['address'] ?? null,
                'patient' => $appointment->user?->full_name,
                'date' => $visit ? verta($visit)->format('l j F Y') : null,
                'time' => $appointment->start_time
                    ? substr((string) $appointment->start_time, 0, 5)
                    : null,
                'kind' => $appointment->kind?->getName(),
                'is_online' => $isOnline,
                'online_chat_id' => $onlineChatId,
                'doctor_avatar' => $appointment->doctor?->avatar,
                // مبلغ به همراه واحد پول، همان طور که هنگام ثبت ذخیره شده
                'fee' => $price['int'] ?? null,
                'fee_label' => isset($price['string'])
                    ? $price['string'].' '.($price['currency'] ?? '')
                    : null,
                // مهلت پرداخت برای نوبت های در انتظار پرداخت
                'pay_deadline' => $appointment->deadline_at
                    ? verta(Carbon::parse($appointment->deadline_at))->format('j F ساعت H:i')
                    : null,
                'phones' => array_values($placeDetail['numbers'] ?? []),
                'lat' => $lat,
                'lng' => $lng,
                'maps_url' => ($lat && $lng)
                    ? 'https://www.google.com/maps/dir/?api=1&destination='.$lat.','.$lng.'&travelmode=driving'
                    : null,
                'can_cancel' => (bool) $canCancel,
                'can_pay' => $status === AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT,
                // پرداخت سمت سرور انجام میشود؛ مسیر جزئیات در قالب جدید خود
                // اپ React است و نباید به خودش برگردد.
                'payment_url' => $status === AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT
                    ? route('api.appointment.payment.create', $appointment)
                    : null,
            ],
        ]);
    }

    /**
     * لغو نوبت توسط خود بیمار.
     * همان قوانین صفحه جزئیات نوبت لایوایر: پیامک لغو و بازسازی کش ساعت ها.
     */
    public function cancelAppointment(string $trackingCode, Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return $this->requestException([
                'status' => false,
                'message' => 'برای لغو نوبت ابتدا وارد حساب کاربری خود شوید.',
                'need_login' => true,
            ]);
        }

        $appointment = AppointmentUser::where('tracking_code', $trackingCode)
            ->where('user_id', $user->id)
            ->first();

        if (! $appointment) {
            return $this->notFound();
        }

        $cancellable = [
            AppointmentUserStatusEnum::STATUS_PENDING,
            AppointmentUserStatusEnum::STATUS_SUCCESSFUL,
            AppointmentUserStatusEnum::STATUS_WAIT_PAYMENT,
            AppointmentUserStatusEnum::STATUS_MONITORING,
        ];

        if (! in_array($appointment->status, $cancellable, true)) {
            return $this->badRequest([
                'status' => false,
                'message' => 'این نوبت قابل لغو نیست.',
            ]);
        }

        $details = $appointment->details ?? [];
        $reason = trim((string) $request->get('reason', ''));
        if ($reason !== '') {
            $details['cancel_reason'] = mb_substr($reason, 0, 500);
        }

        $appointment->update([
            'status' => AppointmentUserStatusEnum::STATUS_CANCEL,
            'details' => $details,
        ]);

        $smsTemplate = setting(SettingKeyEnum::SMS_APPOINTMENT_CANCEL);
        if (! empty($smsTemplate)) {
            try {
                $appointment->notify(new AppointmentSmsNotification($smsTemplate));
            } catch (\Throwable $e) {
                // نبود پیامک نباید مانع لغو نوبت شود
                report($e);
            }
        }

        // ساعت آزادشده باید دوباره در فهرست ظاهر شود
        try {
            $appointment->setting?->runGenerateCacheJob(specialDayConvert($appointment->date_visit));
        } catch (\Throwable $e) {
            report($e);
        }

        return $this->ok([
            'status' => true,
            'message' => 'نوبت شما با موفقیت لغو شد.',
        ]);
    }

    /**
     * پیدا کردن گفتگوی آنلاین متعلق به بیمار جاری، با شناسه نوبت.
     * معادل چک مالکیتی که در فلوی قدیمی (UserChatRoom) کامنت شده بود.
     */
    private function ownedOnlineAppointment(int $onlineAppointmentId, $user): ?AppointmentOnline
    {
        return AppointmentOnline::with(['doctor'])
            ->where('id', $onlineAppointmentId)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * تبدیل یک ردیف پیام به شکلی که اپ نمایش می دهد.
     * نوع پیام (متن/عکس/فایل) بر خلاف داده قدیمی، اینجا از روی mime فایل تعیین میشود.
     */
    private function chatMessagePayload(AppointmentOnlineMessage $m): array
    {
        $mine = $m->type === AppointmentOnlineMessageTypeEnum::QUESTION;
        $file = $m->messageFile->first();

        $imageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/svg+xml', 'image/heic', 'image/heif'];

        $kind = 'text';
        $url = null;
        if ($file) {
            $url = $this->chatFileUrl($file);
            $kind = in_array($file->mime, $imageMimes, true)
                ? 'image'
                : (in_array($file->mime, self::CHAT_AUDIO_FILE_MIMES, true) ? 'voice' : 'file');
        }

        return [
            'id' => $m->id,
            'who' => $mine ? 'me' : 'doctor',
            'kind' => $kind,
            'text' => $m->body,
            'url' => $url,
            'file_name' => $file?->original_name,
            'file_size' => $file?->size ? $this->humanFileSize((int) $file->size) : null,
            'ext' => $file?->extension ? strtoupper($file->extension) : null,
            'time' => $m->created_at?->timestamp,
        ];
    }

    /**
     * فایل های گفتگو داخل public disk اختصاصی tenant قرار دارند و از symlink
     * عمومی /storage قابل دسترسی نیستند. داده های قدیمی نام پوشه را در disk
     * و نام فایل را در path نگه می داشتند؛ هر دو ساختار اینجا پشتیبانی می شوند.
     */
    private function chatFileUrl(AppointmentOnlineMessageFile $file): ?string
    {
        $path = ltrim((string) $file->path, '/');
        if ($path === '') {
            return null;
        }

        $disk = trim((string) $file->disk, '/');
        if ($disk !== '' && $disk !== 'public') {
            $path = $disk.'/'.$path;
        }

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        return tenant_asset($path);
    }

    private function humanFileSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '';
        }
        $units = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت'];
        $i = (int) floor(log($bytes, 1024));
        $i = max(0, min($i, count($units) - 1));

        return round($bytes / (1024 ** $i), 1).' '.$units[$i];
    }

    /**
     * گفتگوی نوبت آنلاین: پیام ها و اطلاعات وضعیت/شمارش معکوس.
     */
    public function chatShow(int $onlineAppointmentId): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return $this->requestException([
                'status' => false,
                'message' => 'برای مشاهده گفتگو ابتدا وارد حساب کاربری خود شوید.',
                'need_login' => true,
            ]);
        }

        $online = $this->ownedOnlineAppointment($onlineAppointmentId, $user);

        if (! $online) {
            return $this->notFound();
        }

        // پیام های جواب داده شده که هنوز دیده نشده اند، با باز شدن گفتگو دیده شده علامت می خورند
        $online->messages()
            ->where('type', AppointmentOnlineMessageTypeEnum::ANSWER)
            ->where('seen', AppointmentOnlineMessageSeenEnum::UNSEEN)
            ->update(['seen' => AppointmentOnlineMessageSeenEnum::SEEN]);

        $messages = $online->messages()->with('messageFile')->orderBy('created_at')->get()
            ->map(fn (AppointmentOnlineMessage $m) => $this->chatMessagePayload($m))
            ->values();

        $canSend = $online->status?->canSendMessage() ?? false;
        $endedAt = $online->ended_at ? Carbon::parse($online->ended_at) : null;

        return $this->ok([
            'status' => true,
            'closed' => ! $canSend,
            'status_label' => $online->status?->getName(),
            'ends_at' => $endedAt?->timestamp,
            'remaining_seconds' => ($canSend && $endedAt && $endedAt->isFuture())
                ? (int) now()->diffInSeconds($endedAt)
                : null,
            'doctor' => [
                'name' => $online->doctor?->full_name,
                'avatar' => $online->doctor?->avatar,
            ],
            'tracking_code' => $online->appointmentUser?->tracking_code,
            'messages' => $messages,
        ]);
    }

    /**
     * ارسال پیام بیمار در گفتگوی نوبت آنلاین: متن و/یا یک فایل (عکس یا فایل عادی).
     */
    public function chatSend(int $onlineAppointmentId, Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return $this->requestException([
                'status' => false,
                'message' => 'برای ارسال پیام ابتدا وارد حساب کاربری خود شوید.',
                'need_login' => true,
            ]);
        }

        $online = $this->ownedOnlineAppointment($onlineAppointmentId, $user);

        if (! $online) {
            return $this->notFound();
        }

        if (! ($online->status?->canSendMessage() ?? false)) {
            return $this->badRequest([
                'status' => false,
                'message' => 'این گفتگو بسته شده و امکان ارسال پیام وجود ندارد.',
            ]);
        }

        $request->validate([
            'message' => 'nullable|string|max:2000',
            // حداکثر ۱۰ مگابایت؛ فقط نوع فایل های مجاز (عکس، سند، صدا) پذیرفته می شوند.
            // mimetypes بر اساس نوع واقعی فایل بررسی می کند، نه فقط پسوند نام آن
            // (چون mp3/webm ضبط شده در مرورگر همیشه با پسوند رایج آن مطابقت ندارد)
            'file' => ['nullable', 'file', 'max:10240', 'mimetypes:'.implode(',', self::CHAT_ALLOWED_FILE_MIMES)],
        ], [
            'file.file' => 'فایل ارسالی معتبر نیست.',
            'file.max' => 'حجم فایل نباید بیشتر از ۱۰ مگابایت باشد.',
            'file.mimetypes' => 'فرمت فایل مجاز نیست. فقط تصویر، سند یا پیام صوتی قابل ارسال است.',
        ]);

        $text = trim((string) $request->get('message', ''));
        $upload = $request->file('file');

        if ($text === '' && ! $upload) {
            return $this->badRequest([
                'status' => false,
                'message' => 'پیام یا فایلی برای ارسال وجود ندارد.',
            ]);
        }

        $message = AppointmentOnlineMessage::create([
            'appointment_online_id' => $online->id,
            'user_id' => $user->id,
            'answer_by' => null,
            'type' => AppointmentOnlineMessageTypeEnum::QUESTION,
            'seen' => AppointmentOnlineMessageSeenEnum::UNSEEN,
            'body' => $text !== '' ? $text : null,
        ]);

        if ($upload) {
            $path = $upload->store('online-message/uploads', 'public');

            AppointmentOnlineMessageFile::create([
                'user_id' => $user->id,
                'answer_by' => null,
                'fk_id' => $message->id,
                'original_name' => $upload->getClientOriginalName(),
                'server_name' => basename($path),
                'disk' => 'public',
                'path' => $path,
                'extension' => $upload->getClientOriginalExtension(),
                'mime' => $upload->getMimeType(),
                'size' => $upload->getSize(),
            ]);
        }

        // پاسخ کاربر: وضعیت گفتگو برای اطلاع پزشک تغییر می کند
        $online->update(['status' => AppointmentOnlineStatusEnum::REPLY_BY_USER]);

        return $this->created([
            'status' => true,
            'message' => $this->chatMessagePayload($message->fresh('messageFile')),
        ]);
    }

    /**
     * گزینه های لازم برای شروع رزرو نوبت یک پزشک.
     * منطق آن با DoctorProfileLivewire یکسان است: ابتدا مطب، سپس بخش،
     * سپس ناحیه (segment) و در صورت وجود، انتخاب اپراتور.
     *
     * اپ در هر مرحله همین مسیر را با انتخاب های قبلی صدا میزند و سرور
     * میگوید مرحله بعد چیست یا اینکه رزرو آماده نمایش ساعت هاست.
     */
    public function bookingOptions(User $doctor, Request $request): \Illuminate\Http\JsonResponse
    {
        if (! $this->doctorIsBookable($doctor)) {
            return $this->ok([
                'status' => true,
                'step' => 'unavailable',
                'message' => 'در حال حاضر امکان دریافت نوبت از این پزشک وجود ندارد.',
            ]);
        }

        $placeId = $request->get('place_id') ?: null;
        $serviceId = $request->get('service_id') ?: null;

        // ۱) مطب: اگر بیش از یکی باشد باید پرسیده شود
        $places = $this->availablePlaces($doctor, $serviceId);

        if ($places->isEmpty()) {
            return $this->ok([
                'status' => true,
                'step' => 'unavailable',
                'message' => 'برای این پزشک مطب فعالی ثبت نشده است.',
            ]);
        }

        if (! $placeId) {
            if ($places->count() > 1) {
                return $this->ok([
                    'status' => true,
                    'step' => 'place',
                    'places' => $places->map(fn ($p) => [
                        'id' => $p->id,
                        'title' => $p->title,
                        'address' => $p->detail['address'] ?? null,
                    ])->values(),
                ]);
            }

            // تنها یک مطب: بدون پرسیدن انتخاب میشود
            $placeId = $places->first()->id;
        } elseif (! $places->contains('id', (int) $placeId)) {
            return $this->badRequest([
                'status' => false,
                'message' => 'مطب انتخابی برای این پزشک معتبر نیست.',
            ]);
        }

        // ۲) بخش (خدمت): متناسب با مطب انتخاب شده
        $services = $this->availableServices($doctor);

        if ($services->isEmpty()) {
            return $this->ok([
                'status' => true,
                'step' => 'unavailable',
                'message' => 'برای این پزشک بخش فعالی ثبت نشده است.',
            ]);
        }

        if (! $serviceId) {
            if ($services->count() > 1) {
                return $this->ok([
                    'status' => true,
                    'step' => 'service',
                    'place_id' => (int) $placeId,
                    'services' => $services->map(fn ($s) => [
                        'id' => $s->id,
                        'title' => $s->title,
                    ])->values(),
                ]);
            }

            $serviceId = $services->first()->id;
        } elseif (! $services->contains('id', (int) $serviceId)) {
            return $this->badRequest([
                'status' => false,
                'message' => 'بخش انتخابی برای این مطب معتبر نیست.',
            ]);
        }

        // ۳) تنظیمات نوبت دهی؛ اگر برای این بخش و مطب نباشد،
        // تنظیمات عمومی پزشک (service_id و place_id برابر null) استفاده میشود
        $setting = AppointmentSetting::findSettingId($doctor->id, $serviceId, $placeId);

        if (! $setting) {
            return $this->ok([
                'status' => true,
                'step' => 'unavailable',
                'message' => 'برای این پزشک تنظیمات نوبت دهی ثبت نشده است.',
            ]);
        }

        // ۴) نوع ویزیت: اگر هم حضوری و هم آنلاین فعال باشد باید پرسیده شود
        $visitTypeInPerson = (bool) ($setting->detail[AppointmentSetting::VISIT_TYPE_INPERSON] ?? false);
        $visitTypeOnline = (bool) ($setting->detail[AppointmentSetting::VISIT_TYPE_ONLINE] ?? false);
        $visitType = $request->get('visit_type') ?: null;

        if ($visitTypeInPerson && $visitTypeOnline && ! $visitType) {
            return $this->ok([
                'status' => true,
                'step' => 'visit_type',
                'place_id' => (int) $placeId,
                'service_id' => (int) $serviceId,
            ]);
        }

        if (! $visitType) {
            $visitType = $visitTypeOnline && ! $visitTypeInPerson ? 'online' : 'in_person';
        }

        if ($visitType === 'online' && ! $visitTypeOnline) {
            return $this->badRequest([
                'status' => false,
                'message' => 'نوبت آنلاین برای این بخش فعال نیست.',
            ]);
        }

        if ($visitType === 'in_person' && ! $visitTypeInPerson) {
            return $this->badRequest([
                'status' => false,
                'message' => 'نوبت حضوری برای این بخش فعال نیست.',
            ]);
        }

        // نوبت آنلاین زمان مشخصی ندارد؛ به جای نمایش روز و ساعت، تاییدیه گرفته میشود
        if ($visitType === 'online') {
            $maxOnlinePerDay = $setting->detail[AppointmentSetting::MAX_ACTIVE_APP_FOR_ONLINE_APP] ?? null;
            $onlineAvailable = true;

            if (is_numeric($maxOnlinePerDay)) {
                $onlineCountTomorrow = AppointmentUser::activeAppointmentStatus()
                    ->onlineAppointment()
                    ->whereDate('date_visit', now()->addDay())
                    ->count();

                $onlineAvailable = $onlineCountTomorrow < (int) $maxOnlinePerDay;
            }

            if (! $onlineAvailable) {
                return $this->ok([
                    'status' => true,
                    'step' => 'unavailable',
                    'message' => 'ظرفیت نوبت‌های آنلاین برای فردا تکمیل شده است، لطفا بعدا دوباره تلاش کنید.',
                ]);
            }

            return $this->ok([
                'status' => true,
                'step' => 'online_ready',
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->full_name,
                'place_id' => (int) $placeId,
                'service_id' => (int) $serviceId,
                'service_title' => $services->firstWhere('id', (int) $serviceId)?->title,
                'description' => setting(SettingKeyEnum::APPOINTMENT_ONLINE_DESCRPTION),
            ]);
        }

        // ۵) ناحیه ها: تک انتخابی یا چند انتخابی
        $segmentIds = array_values(array_filter(
            (array) $request->get('segment_ids', []),
            fn ($id) => is_numeric($id),
        ));

        $segment = $setting->segments()->first();

        if ($segment && $segmentIds === []) {
            $items = $segment->items()
                ->where('display_on_site', true)
                ->orderBy('priority')
                ->get();

            if ($items->isNotEmpty()) {
                return $this->ok([
                    'status' => true,
                    'step' => 'segment',
                    'place_id' => (int) $placeId,
                    'service_id' => (int) $serviceId,
                    // در این جدول multiple_choice برابر ۱ یعنی فقط یک انتخاب
                    'multiple' => (string) $segment->multiple_choice !== '1',
                    'segment_title' => $segment->title ?? null,
                    'segments' => $items->map(fn ($i) => [
                        'id' => $i->id,
                        'title' => $i->title,
                        'time' => $i->time,
                    ])->values(),
                ]);
            }
        }

        // ۶) اپراتور: اگر تنظیمات چند اپراتور داشته باشد باید انتخاب شود
        $operatorId = $request->get('operator_id') ?: null;

        if (! $operatorId && AppointmentSetting::doseSettingHasOperator($setting)) {
            $operators = AppointmentSetting::findOperators($setting);

            if ($operators->count() > 1) {
                return $this->ok([
                    'status' => true,
                    'step' => 'operator',
                    'place_id' => (int) $placeId,
                    'service_id' => (int) $serviceId,
                    'segment_ids' => array_map('intval', $segmentIds),
                    'operators' => $operators->map(fn ($o) => [
                        'id' => $o->id,
                        'title' => $o->full_name,
                    ])->values(),
                ]);
            }

            $operatorId = $operators->first()?->id;
        }

        // همه چیز مشخص است؛ اپ میتواند ساعت ها را بگیرد
        return $this->ok([
            'status' => true,
            'step' => 'ready',
            'doctor_id' => $doctor->id,
            'doctor_name' => $doctor->full_name,
            'place_id' => (int) $placeId,
            'service_id' => (int) $serviceId,
            'segment_ids' => array_map('intval', $segmentIds),
            'operator_id' => $operatorId ? (int) $operatorId : null,
        ]);
    }
    /**
     * گام یک ورود: ارسال کد تایید به شماره موبایل.
     * قوانین همان صفحه ورود لایوایر است: شماره ۱۱ رقمی و فاصله دو دقیقه
     * برای درخواست مجدد.
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $mobile = (string) convert2english(trim((string) $request->get('mobile', '')));

        if (! preg_match('/^09\d{9}$/', $mobile)) {
            return $this->badRequest([
                'status' => false,
                'message' => 'شماره موبایل را به شکل صحیح وارد کنید.',
            ]);
        }

        // ورود بدون کد تایید، اگر در تنظیمات فعال باشد
        if (filter_var(setting(SettingKeyEnum::LOGIN_WITHOUT_OTP), FILTER_VALIDATE_BOOL)) {
            $user = AuthRequest::getUser($mobile);

            if ($this->isStaffAccount($user)) {
                return $this->badRequest([
                    'status' => false,
                    'message' => 'این شماره متعلق به حساب کارکنان است؛ از صفحه ورود پزشکان استفاده کنید.',
                ]);
            }

            Auth::login($user);
            $request->session()->regenerate();

            return $this->ok([
                'status' => true,
                'verified' => true,
                'needs_registration' => $user->isNotRegistered(),
                'user' => $this->currentUserPayload($user),
                // نشست بازسازی شده، پس توکن تازه لازم است
                'csrf_token' => csrf_token(),
            ]);
        }

        $old = AuthRequest::where('mobile', $mobile)->first();

        // اگر هنوز مهلت درخواست بعدی نرسیده باشد
        if ($old && $old->next_request_at && $old->next_request_at->greaterThan(now())) {
            return $this->badRequest([
                'status' => false,
                'message' => 'برای درخواست مجدد کمی صبر کنید.',
                'retry_after' => (int) ceil(now()->diffInSeconds($old->next_request_at)),
            ]);
        }

        AuthRequest::make($mobile, ip());

        return $this->ok([
            'status' => true,
            'message' => 'کد تایید ارسال شد.',
            // شمارش معکوس ارسال دوباره در اپ
            'retry_after' => 120,
        ]);
    }

    /**
     * گام دو ورود: بررسی کد تایید و ورود کاربر.
     */
    public function verify(Request $request): \Illuminate\Http\JsonResponse
    {
        $mobile = (string) convert2english(trim((string) $request->get('mobile', '')));
        $code = (string) convert2english(trim((string) $request->get('code', '')));

        // برخی صفحه کلیدها کد ۰۱۲۳ را به شکل ۱۲۳ میفرستند
        if (preg_match('/^\d{1,4}$/', $code)) {
            $code = str_pad($code, 4, '0', STR_PAD_LEFT);
        }

        if (! preg_match('/^09\d{9}$/', $mobile) || ! preg_match('/^\d{4}$/', $code)) {
            return $this->badRequest([
                'status' => false,
                'message' => 'کد تایید چهار رقمی را وارد کنید.',
            ]);
        }

        if (! AuthRequest::check($mobile, $code)) {
            return $this->badRequest([
                'status' => false,
                'message' => 'کد وارد شده صحیح نیست.',
            ]);
        }

        $user = AuthRequest::getUser($mobile);

        if ($this->isStaffAccount($user)) {
            return $this->badRequest([
                'status' => false,
                'message' => 'این شماره متعلق به حساب کارکنان است؛ از صفحه ورود پزشکان استفاده کنید.',
            ]);
        }

        Auth::login($user);
        // جلوگیری از تثبیت نشست پس از ورود
        $request->session()->regenerate();

        return $this->ok([
            'status' => true,
            'verified' => true,
            // اگر نام ثبت نشده باشد، اپ باید فرم تکمیل ثبت نام را نشان دهد
            'needs_registration' => $user->isNotRegistered(),
            'user' => $this->currentUserPayload($user),
            // نشست بازسازی شده، پس توکن تازه لازم است
            'csrf_token' => csrf_token(),
        ]);
    }

    /**
     * ورود کارکنان مجاز به پنل مدیریت با شماره موبایل و رمز عبور.
     */
    public function adminLogin(Request $request): \Illuminate\Http\JsonResponse
    {
        $mobile = (string) convert2english(trim((string) $request->get('mobile', '')));
        $password = (string) $request->get('password', '');

        if (! preg_match('/^09\d{9}$/', $mobile) || $password === '' || strlen($password) > 255) {
            return $this->badRequest([
                'status' => false,
                'message' => 'شماره موبایل و رمز عبور را به‌درستی وارد کنید.',
            ]);
        }

        $rateKey = 'admin-login:account:'.hash('sha256', $request->getHost().'|'.$mobile);

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $retryAfter = RateLimiter::availableIn($rateKey);

            Log::warning('Admin login temporarily locked', [
                'mobile_hash' => hash('sha256', $mobile),
                'ip' => $request->ip(),
                'host' => $request->getHost(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'تعداد تلاش‌های ورود بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.',
                'retry_after' => $retryAfter,
            ], 429)->header('Retry-After', (string) $retryAfter);
        }

        $user = User::where('mobile', $mobile)->first();
        $passwordIsValid = Hash::check($password, $user?->password ?? self::DUMMY_ADMIN_PASSWORD_HASH);

        if (! $user || ! $passwordIsValid || ! $user->can('ADMIN_ACCESS')) {
            RateLimiter::hit($rateKey, 300);

            Log::warning('Admin login failed', [
                'mobile_hash' => hash('sha256', $mobile),
                'ip' => $request->ip(),
                'host' => $request->getHost(),
            ]);

            return $this->badRequest([
                'status' => false,
                'message' => 'شماره موبایل یا رمز عبور صحیح نیست.',
            ]);
        }

        RateLimiter::clear($rateKey);

        // همانند صفحه قبلی ورود پزشک، پروفایل تایید نشده اجازه ورود ندارد
        if (! empty($user->ban_user)) {
            return $this->badRequest([
                'status' => false,
                'message' => 'پروفایل شما هنوز تایید نشده است!',
            ]);
        }

        Auth::login($user, false);
        $request->session()->regenerate();

        Log::info('Admin login succeeded', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'host' => $request->getHost(),
        ]);

        return $this->ok([
            'status' => true,
            'redirect' => route('admin.dashboard'),
            'csrf_token' => csrf_token(),
        ]);
    }

    /**
     * تکمیل ثبت نام کاربری که تازه وارد شده و نامش ثبت نشده است.
     */
    public function completeRegistration(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return $this->requestException([
                'status' => false,
                'message' => 'ابتدا وارد حساب کاربری خود شوید.',
                'need_login' => true,
            ]);
        }

        $firstName = trim((string) $request->get('first_name', ''));
        $lastName = trim((string) $request->get('last_name', ''));

        if ($firstName === '' || $lastName === '') {
            return $this->badRequest([
                'status' => false,
                'message' => 'نام و نام خانوادگی را وارد کنید.',
            ]);
        }

        $nationalCode = (string) convert2english(trim((string) $request->get('national_code', '')));
        if ($nationalCode !== '' && ! preg_match('/^\d{10}$/', $nationalCode)) {
            return $this->badRequest([
                'status' => false,
                'message' => 'کد ملی باید ۱۰ رقم باشد.',
            ]);
        }
        if ($nationalCode === '' && ! $user->national_code
            && filter_var(setting(SettingKeyEnum::USER_REGISTER_NATIONAL_CODE_REQUIRED), FILTER_VALIDATE_BOOL)) {
            return $this->badRequest([
                'status' => false,
                'message' => 'وارد کردن کد ملی الزامی است.',
            ]);
        }

        $birthYear = (string) convert2english(trim((string) $request->get('birthday_year', '')));
        $birthMonth = (string) convert2english(trim((string) $request->get('birthday_month', '')));
        $birthDay = (string) convert2english(trim((string) $request->get('birthday_day', '')));
        $hasBirthdayInput = $birthYear !== '' || $birthMonth !== '' || $birthDay !== '';
        if ($hasBirthdayInput && (! preg_match('/^\d{4}$/', $birthYear)
            || ! preg_match('/^\d{1,2}$/', $birthMonth)
            || ! preg_match('/^\d{1,2}$/', $birthDay)
            || (int) $birthYear < 1300 || (int) $birthYear > 1500
            || (int) $birthMonth < 1 || (int) $birthMonth > 12
            || (int) $birthDay < 1
            || (int) $birthDay > ((int) $birthMonth <= 6 ? 31 : 30))) {
            return $this->badRequest([
                'status' => false,
                'message' => 'تاریخ تولد را به شکل صحیح وارد کنید.',
            ]);
        }
        if (! $hasBirthdayInput && ! $user->birthday
            && filter_var(setting(SettingKeyEnum::USER_REGISTER_BIRTHDAY_REQUIRED), FILTER_VALIDATE_BOOL)) {
            return $this->badRequest([
                'status' => false,
                'message' => 'وارد کردن تاریخ تولد الزامی است.',
            ]);
        }

        $user->first_name = mb_substr($firstName, 0, 225);
        $user->last_name = mb_substr($lastName, 0, 225);
        if ($nationalCode !== '') {
            $user->national_code = $nationalCode;
        }
        if ($hasBirthdayInput) {
            $user->birthday = json_encode([
                'year' => (int) $birthYear,
                'month' => (int) $birthMonth,
                'day' => (int) $birthDay,
            ]);
        }

        if (! $user->document_number) {
            $user->document_number = User::generateDocumentNumber();
        }

        // مقادیر متا با همان انتساب ذخیره میشوند؛ save() روی جدول users خطا میدهد

        return $this->ok([
            'status' => true,
            'user' => $this->currentUserPayload($user),
        ]);
    }

    /**
     * خروج از حساب کاربری.
     */
    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->ok(['status' => true]);
    }
    /**
     * ویرایش اطلاعات بیمار از صفحه پروفایل.
     * فقط فیلدهایی که کاربر فرستاده تغییر میکنند.
     */
    public function updateProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return $this->requestException([
                'status' => false,
                'message' => 'ابتدا وارد حساب کاربری خود شوید.',
                'need_login' => true,
            ]);
        }

        $first = trim((string) $request->get('first_name', ''));
        $last = trim((string) $request->get('last_name', ''));

        if ($first === '' || $last === '') {
            return $this->badRequest([
                'status' => false,
                'message' => 'نام و نام خانوادگی را وارد کنید.',
            ]);
        }

        $user->first_name = mb_substr($first, 0, 225);
        $user->last_name = mb_substr($last, 0, 225);

        // کد ملی اختیاری است؛ در صورت ارسال باید ۱۰ رقمی باشد
        if ($request->has('national_code')) {
            $code = (string) convert2english(trim((string) $request->get('national_code', '')));

            if ($code !== '') {
                if (preg_match('/^\d{1,10}$/', $code)) {
                    $code = str_pad($code, 10, '0', STR_PAD_LEFT);
                }

                if (! preg_match('/^\d{10}$/', $code)) {
                    return $this->badRequest([
                        'status' => false,
                        'message' => 'کد ملی باید ۱۰ رقم باشد.',
                    ]);
                }

                $user->national_code = $code;
            }
        }

        if ($request->filled('city')) {
            $user->city = mb_substr(trim((string) $request->get('city')), 0, 225);
        }

        if ($request->filled('gender')) {
            $user->gender = mb_substr(trim((string) $request->get('gender')), 0, 50);
        }

        // مقادیر متا با همان انتساب ذخیره میشوند؛ save() روی جدول users خطا میدهد

        return $this->ok([
            'status' => true,
            'message' => 'اطلاعات شما ذخیره شد.',
            'user' => $this->currentUser(),
            'info' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'mobile' => $user->mobile,
                'national_code' => $user->national_code,
                'birthday' => $this->birthdayLabel($user),
                'gender' => $user->gender,
                'city' => $user->city,
            ],
        ]);
    }
    // ---------------------------------------------------------------
    // بخش های کمکی
    // ---------------------------------------------------------------

    private function siteInfo(): array
    {
        $logo = setting(SettingKeyEnum::SITE_LOGO_URL);

        return [
            'title' => setting(SettingKeyEnum::SITE_TITLE),
            'subtitle' => setting(SettingKeyEnum::SITE_SLIDER_TITLE),
            'logo' => $logo ? assetStorage($logo) : null,
            'footerDescription' => setting(SettingKeyEnum::FOOTER_DESCRIPTION),
            'instagram' => setting(SettingKeyEnum::INSTAGRAM_ADDRESS),
            'whatsapp' => setting(SettingKeyEnum::WHATSAPP_ADDRESS),
            'telegram' => setting(SettingKeyEnum::TELEGRAM_ADDRESS),
            'enamad' => enamad_html(setting(SettingKeyEnum::FOOTER_ENAMAD)),
            'hideFooter' => filter_var(setting(SettingKeyEnum::DISABLE_FOOTER_DISPLAY), FILTER_VALIDATE_BOOL),
            // «غیر فعال سازی ui برای بیماران»؛ هدر صفحه جزئیات نوبت پنهان میشود
            'patientUiDisabled' => filter_var(setting(SettingKeyEnum::DISABLE_UI_FOR_VOIP_ONLY_APPOINTMENT), FILTER_VALIDATE_BOOL),
            'appointmentEnabled' => filter_var(setting(SettingKeyEnum::APPOINTMENT_STATUS), FILTER_VALIDATE_BOOL),
            'nationalCodeRequired' => filter_var(setting(SettingKeyEnum::USER_REGISTER_NATIONAL_CODE_REQUIRED), FILTER_VALIDATE_BOOL),
            'birthdayRequired' => filter_var(setting(SettingKeyEnum::USER_REGISTER_BIRTHDAY_REQUIRED), FILTER_VALIDATE_BOOL),
            'mapIrApiKey' => config('services.map_ir.api_key'),
            // لینک ثبت نام پزشک در صفحه ورود پزشک
            'doctorRegistration' => ! disableUi()
                && filter_var(setting(SettingKeyEnum::ENABLE_DOCTOR_REGISTRATION), FILTER_VALIDATE_BOOL),
        ];
    }

    /**
     * تصاویر قالب جدید، فقط آنهایی که حالت فعلی لازم دارد.
     */
    private function templateImages(): array
    {
        $map = [
            SettingKeyEnum::NEW_TPL_HERO_IMAGE->value => 'hero',
            SettingKeyEnum::NEW_TPL_DOCTOR_AVATAR->value => 'doctor_avatar',
            SettingKeyEnum::NEW_TPL_CLINIC_LOGO->value => 'clinic_logo',
            SettingKeyEnum::NEW_TPL_ABOUT_IMAGE->value => 'about',
            SettingKeyEnum::NEW_TPL_TEAM_IMAGE->value => 'team',
            SettingKeyEnum::NEW_TPL_DEVICE_IMAGE->value => 'device',
            SettingKeyEnum::NEW_TPL_DEPARTMENT_IMAGE->value => 'department',
            SettingKeyEnum::NEW_TPL_BOOKING_BANNER_IMAGE->value => 'booking_banner',
        ];

        $needed = AppointmentModeEnum::current()->imageSettings();
        $rows = Setting::getSettingByArray($needed);

        $images = [];
        foreach ($rows as $row) {
            $key = $map[$row->setting_key->value] ?? null;
            if ($key && $row->setting_value) {
                $images[$key] = assetStorage($row->setting_value);
            }
        }

        return $images;
    }

    private function currentUser(): ?array
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->full_name,
            'mobile' => $user->mobile,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'national_code' => $user->national_code,
            'birthday' => $this->birthdayLabel($user),
            'avatar' => $user->getUserAvatar(),
        ];
    }

    /**
     * پزشکانی که در صفحه اصلی نمایش داده می شوند.
     * اگر هیچ پزشکی برای «معرفی» علامت نخورده باشد، همه پزشکان فعال نمایش داده
     * می شوند تا صفحه اصلی خالی نماند.
     */
    private function listedDoctors(): \Illuminate\Support\Collection
    {
        $listed = User::introductionDoctors()->get()
            ->sortBy(fn (User $u) => $u->dr_info_order ?? PHP_INT_MAX);

        if ($listed->isNotEmpty()) {
            return $listed->values();
        }

        return User::doctors_query()
            ->whereHas('metas', function ($q) {
                $q->where('meta_key', UserMetaEnum::ACTIVE_APPOINTMENT)->where('meta_value', true);
            })
            ->get()
            ->values();
    }

    /**
     * پزشک اصلی مطب در حالت های تک پزشک.
     */
    private function primaryDoctor(): ?User
    {
        $doctors = $this->listedDoctors();

        $selectedId = (int) setting(SettingKeyEnum::NEW_TPL_PRIMARY_DOCTOR);
        if ($selectedId) {
            $selected = $doctors->firstWhere('id', $selectedId);
            if ($selected) {
                return $selected;
            }
        }

        return $doctors->first();
    }

    private function doctorPayload(User $doctor): array
    {
        return [
            'id' => $doctor->id,
            'name' => $doctor->full_name,
            'specialty' => $doctor->DocSpecialities() ?: null,
            'avatar' => $doctor->getUserAvatar(),
            'biography' => $doctor->drBiography,
            'licence_number' => $doctor->drLicenceNumber,
            'address' => $doctor->drAddress,
        ];
    }

    private function doctorStats(User $doctor): array
    {
        return [
            'rating' => $doctor->drRate,
            'experience' => setting(SettingKeyEnum::NEW_TPL_DOCTOR_EXPERIENCE),
            'patients' => setting(SettingKeyEnum::NEW_TPL_PATIENTS_COUNT),
            'place_count' => $doctor->places()->count(),
        ];
    }

    /**
     * سوابق صفحهٔ اصلی؛ هر خط با قالب «سال | شرح» ذخیره می‌شود.
     */
    private function doctorTimeline(?string $education = null): array
    {
        $education ??= (string) setting(SettingKeyEnum::NEW_TPL_DOCTOR_EDUCATION);

        return collect(preg_split('/\R/u', $education))
            ->map(function (string $line): ?array {
                $line = trim($line);
                if ($line === '') {
                    return null;
                }

                $parts = preg_split('/\s*[|｜]\s*/u', $line, 2);

                return count($parts) === 2
                    ? ['year' => trim($parts[0]), 'text' => trim($parts[1])]
                    : ['year' => null, 'text' => $line];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * متن ها و دکمه های هدر اصلی صفحه اول. هر مقدار خالی، از پزشک اصلی
     * (نام، تخصص، بیوگرافی) یا مقدار پیش فرض جایگزین میشود.
     */
    private function heroContent(?User $owner = null): array
    {
        $phone = setting(SettingKeyEnum::NEW_TPL_HERO_PHONE);

        return [
            'badge' => setting(SettingKeyEnum::NEW_TPL_HERO_BADGE_TEXT),
            'title' => setting(SettingKeyEnum::NEW_TPL_HERO_TITLE) ?: $owner?->full_name,
            'subtitle' => setting(SettingKeyEnum::NEW_TPL_HERO_SUBTITLE) ?: $owner?->DocSpecialities(),
            'description' => setting(SettingKeyEnum::NEW_TPL_HERO_DESCRIPTION) ?: $owner?->drBiography,
            'primary_button' => setting(SettingKeyEnum::NEW_TPL_HERO_PRIMARY_BUTTON_TEXT) ?: 'دریافت نوبت آنلاین',
            'secondary_button' => setting(SettingKeyEnum::NEW_TPL_HERO_SECONDARY_BUTTON_TEXT) ?: 'تماس با مطب',
            'phone' => $phone,
        ];
    }

    /**
     * ساعات هفتگی مطب با اولویت تنظیمات اختصاصی مکان و سپس تنظیمات عمومی پزشک؛
     * روزهای استثنا در این برنامهٔ هفتگی نمایش داده نمی‌شوند.
     */
    private function workingHours(User $doctor, ?int $placeId = null, bool $includeClosedDays = false): array
    {
        $query = AppointmentSetting::where('user_id', $doctor->id)
            ->active()
            ->whereNull('service_id')
            ->whereNull('place_id');
        $setting = $placeId === null ? null : AppointmentSetting::where('user_id', $doctor->id)
            ->active()->whereNull('service_id')->where('place_id', $placeId)->first();
        $setting ??= $query->first();

        if (! $setting) {
            return [];
        }

        $weeklyTimes = $setting->times()
            ->whereNull('special_date')
            ->orderBy('start_at')
            ->get()
            ->groupBy(fn ($t) => $t->day_number->value)
            ->sortKeys();

        // No recurring schedule is different from a configured week with days off.
        if ($includeClosedDays && $weeklyTimes->isNotEmpty()) {
            $weeklyTimes = collect(AppintmentSettingDayNumber::cases())
                ->mapWithKeys(fn ($day) => [$day->value => $weeklyTimes->get($day->value, collect())]);
        }

        return $weeklyTimes
            ->map(function ($times, $dayNumber) {
                $day = AppintmentSettingDayNumber::from((int) $dayNumber);

                return [
                    'day_number' => (int) $dayNumber,
                    'day_name' => $day->getName(),
                    'ranges' => $times->map(fn ($t) => [
                        'start' => substr($t->start_at, 0, 5),
                        'end' => substr($t->end_at, 0, 5),
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  iterable<User>  $doctors
     */
    private function doctorList(iterable $doctors): array
    {
        $rows = [];
        foreach ($doctors as $doctor) {
            $rows[] = array_merge(
                $this->doctorPayload($doctor),
                ['rating' => $doctor->drRate, 'place_count' => $doctor->places()->count()],
            );
        }

        return $rows;
    }

    /**
     * خدمات صفحه اصلی در حالت های تک پزشک. برخلاف پروفایل هر پزشک که فقط
     * خدمات همان پزشک را نشان میدهد، اینجا خدمات همه پزشکان فعال جمع میشود
     * تا وجود چند پزشک باعث پنهان ماندن خدمات پزشکان دیگر نشود.
     *
     * @param \Illuminate\Support\Collection<int, User> $doctors
     */
    private function homeServices(\Illuminate\Support\Collection $doctors): array
    {
        $rows = [];
        $seen = [];

        foreach ($doctors as $doctor) {
            foreach ($this->services($doctor) as $service) {
                if (isset($seen[$service['id']])) {
                    continue;
                }
                $seen[$service['id']] = true;
                $rows[] = $service;
            }
        }

        return $rows;
    }

    private function services(User $doctor): array
    {
        // قیمت و مدت ویزیت در تنظیمات نوبت دهی همان پزشک/خدمت ذخیره میشود
        $settings = $doctor->appointmentSettings()
            ->where('active', ActiveEnum::ACTIVE->value)
            ->get()
            ->keyBy('service_id');

        return $doctor->services()
            ->whereNull('parent_id')
            ->active()
            ->showToUser()
            ->orderBy('priority')
            ->get()
            ->map(function (Service $s) use ($settings, $doctor) {
                $setting = $settings->get($s->id);
                $minutes = $setting?->time_for_visit;

                return [
                    'id' => $s->id,
                    'doctor_id' => $doctor->id,
                    'name' => $s->title,
                    'image' => $this->serviceImage($s),
                    'price' => $this->servicePrice($setting),
                    'duration' => $minutes ? $minutes . ' دقیقه' : null,
                    'description' => null,
                ];
            })->all();
    }

    /**
     * تصویر خدمت. در ستون icon مسیر نسبی فایل ذخیره میشود.
     * اگر خدمتی تصویر نداشته باشد null برمیگردد تا سمت اپ جای خالی نمایش داده شود.
     */
    private function serviceImage(Service $service): ?string
    {
        $icon = $service->icon;
        if (! $icon) {
            return null;
        }

        return \Illuminate\Support\Str::startsWith($icon, ['http://', 'https://'])
            ? $icon
            : assetStorage($icon);
    }

    /**
     * قیمت خدمت. ساختار در detail به شکل payment.{inPerson|online|voip}.price است.
     * اولین قیمتی که ثبت شده باشد برگردانده میشود.
     */
    private function servicePrice(?AppointmentSetting $setting): ?string
    {
        $payment = $setting?->detail[AppointmentSetting::PAYMENT] ?? null;
        if (! is_array($payment)) {
            return null;
        }

        foreach ([AppointmentSetting::IN_PERSON, AppointmentSetting::ONLINE, AppointmentSetting::VOIP] as $type) {
            $price = $payment[$type][AppointmentSetting::PRICE] ?? null;
            if ($price !== null && $price !== '') {
                return (string) $price;
            }
        }

        return null;
    }

    private function places(User $doctor, bool $withWorkingHours = false): array
    {
        return $doctor->places()->active()->orderBy('priority')->get()
            ->map(fn (Place $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'address' => $p->detail[Place::DETAIL_ADDRESS] ?? null,
                'phone' => $p->detail[Place::DETAIL_KEY_NUMBERS] ?? null,
                'latitude' => $p->detail[Place::DETAIL_KEY_LOCATION][Place::DETAIL_KEY_LOCATION_LAT] ?? null,
                'longitude' => $p->detail[Place::DETAIL_KEY_LOCATION][Place::DETAIL_KEY_LOCATION_LNG] ?? null,
                ...($withWorkingHours ? ['working_hours' => $this->workingHours($doctor, $p->id, true)] : []),
            ])->all();
    }

    private function specialities(): array
    {
        return Speciality::query()->get()
            ->map(fn ($s) => ['id' => $s->id, 'title' => $s->title])
            ->all();
    }

    /**
     * پزشکان صفحه اصلی کلینیک، به همراه بخش و نزدیک‌ترین زمان آزاد هر پزشک.
     */
    private function clinicDoctorRows(\Illuminate\Support\Collection $doctors): array
    {
        $rows = [];
        foreach ($doctors as $doctor) {
            $row = $this->doctorList([$doctor])[0];
            $row['department'] = $doctor->services()->whereNull('services.parent_id')->value('services.title')
                ?? $doctor->services()->value('services.title');
            $row['department_ids'] = $doctor->services()->pluck('services.id')->all();
            $row['speciality_ids'] = $doctor->specialities()->pluck('specialities.id')->all();
            $row['next'] = $this->nextFreeSlots($doctor);
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * نزدیک‌ترین روز آزاد پزشک و چند ساعت اول آن؛ برای کم شدن بار، چند دقیقه کش می‌شود.
     */
    private function nextFreeSlots(User $doctor): ?array
    {
        return \Illuminate\Support\Facades\Cache::remember(
            'tenant_next_slot_' . (tenant()?->getTenantKey() ?? 'x') . '_' . $doctor->id,
            now()->addMinutes(5),
            function () use ($doctor) {
                try {
                    $setting = $this->resolveAppointmentSetting($doctor, null);
                    if (! $setting) {
                        return null;
                    }
                    $days = $this->buildDays(app('AppointmentUserService')->listAppointments($setting), $setting);
                    foreach ($days as $day) {
                        $free = array_values(array_filter($day['times'], fn ($t) => $t['free']));
                        if ($free) {
                            return [
                                'date' => $day['date'],
                                'label' => $day['label'],
                                'slots' => array_slice(array_column($free, 'label'), 0, 6),
                            ];
                        }
                    }
                } catch (\Throwable) {
                    // اگر محاسبه زمان آزاد ممکن نبود، فقط زمان نمایش داده نمیشود
                }

                return null;
            }
        );
    }

    private function clinicSpecialities(\Illuminate\Support\Collection $doctors): array
    {
        return Speciality::query()->get()->map(fn ($s) => [
            'id' => $s->id,
            'title' => $s->title,
            'doctors_count' => $doctors->filter(
                fn (User $d) => $d->specialities->contains('id', $s->id)
            )->count(),
        ])->all();
    }

    private function clinicDepartments(\Illuminate\Support\Collection $doctors): array
    {
        return collect($this->departments())->map(function (array $dep) use ($doctors) {
            $dep['doctors_count'] = $doctors->filter(
                fn (User $d) => $d->services->contains('id', $dep['id'])
            )->count();
            $dep['description'] = Service::find($dep['id'])?->description;

            return $dep;
        })->all();
    }

    private function clinicStats(array $doctors, array $departments): array
    {
        $rates = collect($doctors)->pluck('rating')->filter(fn ($r) => is_numeric($r) && $r > 0);

        return [
            'doctors' => count($doctors),
            'departments' => count($departments),
            'monthly_appointments' => AppointmentUser::where('created_at', '>=', now()->subMonth())->count(),
            'rating' => $rates->isNotEmpty() ? round($rates->avg(), 1) : null,
        ];
    }

    private function departments(): array
    {
        return Service::query()->whereNull('parent_id')->active()->showToUser()->orderBy('priority')->get()
            ->map(fn (Service $s) => [
                'id' => $s->id,
                'title' => $s->title,
                'image' => $s->icon ?: null,
            ])->all();
    }

    /**
     * استان/شهرهای سطح اول، برای فیلتر «انتخاب موقعیت» در صفحه اصلی و جستجو.
     */
    private function provinceList(): array
    {
        return Province::whereNull('parent_id')->orderBy('sort')->get(['id', 'title'])
            ->map(fn (Province $p) => ['id' => $p->id, 'title' => $p->title])
            ->all();
    }

    /**
     * آیا این پزشک مطبی در استان انتخابی دارد.
     * معادل فیلتر province در SearchPage::searchIn نسخه قدیمی.
     */
    private function doctorInProvince(User $doctor, int $provinceId): bool
    {
        return $doctor->places()
            ->whereJsonContains('detail->' . Place::DETAIL_PROVINCE, $provinceId)
            ->exists();
    }

    private function gallery(User $doctor): array
    {
        $raw = $doctor->dr_gallery;
        if (! $raw) {
            return [];
        }

        $items = json_decode($raw, true);
        if (! is_array($items)) {
            return [];
        }

        $out = [];
        foreach ($items as $i => $src) {
            $out[] = ['id' => $i, 'file_address' => $src];
        }

        return $out;
    }

    private function comments(?int $doctorId = null): array
    {
        return Comment::query()
            ->with('user')
            ->where('status', CommentStatusEnum::ACCEPTED)
            ->when($doctorId === null, fn ($q) => $q->where('show_in_homePage', CommentShowHomePage::SHOW))
            ->when($doctorId !== null, fn ($q) => $q->where('doctor_id', $doctorId))
            ->latest()
            ->take(6)
            ->get()
            ->map(fn (Comment $c) => [
                'id' => $c->id,
                'body' => $c->body,
                'author' => $c->user?->full_name ?? 'کاربر',
                'star' => $c->star,
                'date' => $c->created_at ? verta($c->created_at)->format('Y/m/d') : null,
            ])->all();
    }
}

