<?php

namespace Modules\MigrateOldData\App\Console;

use App\Enum\ActiveEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Google\ApiCore\ResourceTemplate\Segment;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\AppointmentSetting\app\Models\AppointmentSegment;
use Modules\AppointmentSetting\app\Models\AppointmentSetting;
use Modules\AppointmentSetting\app\Models\AppointmentSegmentItem;
use Modules\AppointmentSetting\app\Enum\AppintmentSettingDayNumber;

class MigrateAppointmentSetting extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrateData:appointment_setting';

    /**
     * The console command description.
     */
    protected $description = 'migrate doctor data.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $previousDefaultConnection = DB::getDefaultConnection();

        try {
            DB::setDefaultConnection('new_mysql');
            DB::connection('new_mysql')->transaction(fn () => $this->generalSetting());
        } finally {
            DB::setDefaultConnection($previousDefaultConnection);
        }
    }
    private function generalSetting()
    {
        // Connect to the old database
        $oldData = DB::connection('old_mysql')->table('appointments')->get();

        // Loop through each record and transform it
        foreach ($oldData as $data) {
            // Transform the data according to new structure
            $Old_timesettings = json_decode($data->time_for_visit, true);
            $old_otherSetting = json_decode($data->setting, true);
            $cost = json_decode($data->cost, true)['FREE']['COST'][6]['IN_PERSON'];
            $interference = json_decode($data->interference, true)['STATUS'];
            $newData = [
                'user_id' => $data->user_id,
                'service_id' => $data->appointment_part_id,
                'place_id' => $data->appointment_office_id,
                'time_for_visit' => $Old_timesettings['PUBLIC_TIME'],
                'min_day_active' => $old_otherSetting['DAY_BEFORE'],
                'max_day_active' => $old_otherSetting['DAY_AFTER'],
                'cancellation_by_user' => $old_otherSetting['CANCEL_BY_USER'],
                'active_payment' => $cost != 0 ? 1 : 0,
                'interference' => $interference == true ? 1 : 0,
                'active' => ActiveEnum::ACTIVE,
                'detail' => $this->createDetail($data, $cost),
            ];

            $settingKey = [
                'user_id' => $data->user_id,
                'service_id' => $data->appointment_part_id,
                'place_id' => $data->appointment_office_id,
            ];
            DB::connection('new_mysql')->table('appointment_settings')->updateOrInsert($settingKey, $newData);
            $appointment_setting = DB::connection('new_mysql')
                ->table('appointment_settings')
                ->where($settingKey)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->first();

            $this->insertTimes($appointment_setting->id, $data);
            $this->segmnents($appointment_setting->id, $data);
        }

        $this->info('appointment_setting migration completed successfully.');
    }
    private function createDetail($data, $cost)
    {
        $detail = [
            AppointmentSetting::VISIT_TYPE_INPERSON                  => true,
            AppointmentSetting::VISIT_TYPE_VOIP                      => null,
            AppointmentSetting::VISIT_TYPE_ONLINE                    => null,
            AppointmentSetting::MAX_AVAILABLE_APPOINTMENT_EACH_DAY   =>  null,
            AppointmentSetting::MAX_AVAILABLE_APPOINTMENT_FOR_SECRETERY     => null,
            AppointmentSetting::MONITORTING_APPOINTMENT              => null,
            AppointmentSetting::OPERATORS => [
                AppointmentSetting::STATUS => false,
                AppointmentSetting::IDS    =>  null,
            ],
            AppointmentSetting::PAYMENT                              =>
            [
                AppointmentSetting::STATUS                           => $cost > 0 ? true  : false,
                AppointmentSetting::NOT_PAYING_STATUS                => 'dontSubmit',
                AppointmentSetting::ONLINE =>
                [
                    AppointmentSetting::STATUS                       => $cost > 0 ? true : null,
                    AppointmentSetting::PRICE                        => $cost > 0  ? $cost  : null,
                ],
                AppointmentSetting::VOIP  =>
                [
                    AppointmentSetting::STATUS                       => null,
                    AppointmentSetting::PRICE                        =>  null,
                ],
                AppointmentSetting::IN_PERSON  =>
                [
                    AppointmentSetting::STATUS                       =>  null,
                    AppointmentSetting::PRICE                        =>  null,
                ],
            ]
        ];
        return json_encode($detail);
    }
    private function insertTimes($appointment_setting_id, $data)
    {
        $days = json_decode($data->content);
        $appointment_setting =  DB::connection('new_mysql')->table('appointment_settings')->where('id', $appointment_setting_id)->first();
        DB::connection('new_mysql')->table('appointment_setting_times')
            ->where('appointment_setting_id', $appointment_setting->id)
            ->whereNull('special_date')
            ->delete();

        foreach ($days as $dayName => $dayTime) {
            if ($dayTime->STATUS == false) {
                continue;
            }

            foreach (($dayTime->TIME ?? []) as $timeRange) {
                $startAt = $timeRange->FROM ?? '00:00';
                $endAt = $timeRange->TO ?? '00:00';
                if ($startAt === '00:00' || $endAt === '00:00' || $startAt === $endAt) {
                    continue;
                }

                $app_setting_times = [
                    'appointment_setting_id' => $appointment_setting->id,
                    'day_number' => $this->findDayName($dayName),
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                ];
                DB::connection('new_mysql')->table('appointment_setting_times')->insert($app_setting_times);
            }
        }
    }
    private function findDayName($dayName)
    {
        return  AppintmentSettingDayNumber::shortNameForDayTonumber($dayName);
    }
    private function calculateStartTime($dayTime)
    {
        $start_time = '00:00';
        if (isset($dayTime->TIME)) {
            foreach ($dayTime->TIME as $times) {
                if ($times->FROM == '00:00' || $times->TO == '00:00') {
                    continue;
                } elseif ($times->FROM != null) {

                    $start_time = $times->FROM;
                    break;
                }
            }
        }
        return $start_time;
    }
    private function calculateEndTime($dayTime)
    {
        $end_time = '00:00';
        if (isset($dayTime->TIME) && ! is_null($end_time)) {
            foreach ($dayTime->TIME as $times) {
                if ($times->TO == '00:00' || $times->TO == '00:00') {
                    continue;
                } elseif ($times->FROM != null) {
                    $end_time = $times->TO;
                    break;
                }
            }
        }
        return $end_time;
    }
    private function segmnents($appointment_setting_id, $data)
    {
        $segment = json_decode($data->time_for_visit, true) ?: [];
        $appointment_setting =  DB::connection('new_mysql')->table('appointment_settings')->where('id', $appointment_setting_id)->first();
        if (($segment['STATUS'] ?? false) == true) {
            if (DB::connection('new_mysql')->table('appointment_segment_setting')
                ->where('appointment_setting_id', $appointment_setting->id)->exists()) {
                return;
            }
            $serviceTitle = DB::connection('new_mysql')
                ->table('services')
                ->where('id', $appointment_setting->service_id)
                ->value('title');

            $segmentObj =  AppointmentSegment::create([
                'title' => ($serviceTitle ?: 'خدمت') . ' زمانبندی',
                'multiple_choice' => ($segment['SELECT_TYPE'] ?? null) == 'multi' ? 0 : 1,
                'active' => 1,
            ]);
            DB::connection('new_mysql')->table('appointment_segment_setting')->insert([
                'appointment_setting_id' => $appointment_setting->id,
                'appointment_segment_id' => $segmentObj->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach (($segment['TIME'] ?? []) as $segmentItems) {
                $segmentObj->items()->create([
                    'title' => $segmentItems['TITLE'],
                    'display_on_site' => $segmentItems['DISPLAY_ON'],
                    'price' => $segmentItems['COST'],
                    'priority' => AppointmentSegmentItem::maxPriority(),
                    'time' => $segmentItems['TIME'],
                ]);
            }
        }
    }
}
