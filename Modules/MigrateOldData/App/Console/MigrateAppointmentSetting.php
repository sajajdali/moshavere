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
        $this->generalSetting();
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

            // Insert the transformed data into the new database and get the new ID
            $newId = DB::connection('new_mysql')->table('appointment_settings')->insertGetId($newData);
            // Retrieve the newly created record
            $appointment_setting = DB::connection('new_mysql')->table('appointment_settings')->find($newId);

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
        foreach ($days as $dayName => $dayTime) {
            if ($dayTime->STATUS == false) {
                continue;
            }
            $app_setting_times = [
                'appointment_setting_id'            => $appointment_setting->id,
                'day_number'                        => $this->findDayName($dayName),
                'start_at'                          => $this->calculateStartTime($dayTime),
                'end_at'                            => $this->calculateEndTime($dayTime) ?? "00:00",
            ];
            if ($app_setting_times['start_at'] == '00:00' && $app_setting_times['end_at'] == '00:00') {
                continue;
            } else {
                DB::connection('new_mysql')->table('appointment_setting_times')->insert($app_setting_times);
                // $appointment_setting->times()->create($app_setting_times);
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
        $segment = json_decode($data->time_for_visit, true);
        $appointment_setting =  DB::connection('new_mysql')->table('appointment_settings')->where('id', $appointment_setting_id)->first();
        if ($segment['STATUS'] == true) {
            $segmentObj =  AppointmentSegment::create([
                'title' => $appointment_setting->service?->title  . ' زمانبندی',
                'multiple_choice' => $segment['SELECT_TYPE'] == 'multi' ? 0 : 1,
                'active' => 1,
            ]);
            $appointment_setting->segments()->attach($segmentObj->id);
            foreach ($segment['TIME'] as $segmentItems) {
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
