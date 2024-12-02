<?php

namespace Modules\AppointmentUser\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;

class CompeleteOnlineAppointment extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'appointment:check-end-at';

    /**
     * The console command description.
     */
    protected $description = 'compelete the appointment that hasnet awnser with in end time .';

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
        AppointmentOnline::where('ended_at', '<', now())
        ->update(
            [
                'status' => AppointmentOnlineStatusEnum::COMPLETED_BY_DOCTOR,
                'ended_at' => null  ,
                'close_at' => now()  ,
            ]);
        Log::info('appointmentonline ended_at has been checked');
    }
}
