<?php

namespace Modules\AppointmentUser\app\Console;

use Illuminate\Console\Command;
use Modules\AppointmentUser\app\Models\AppointmentOnline;
use Modules\AppointmentUser\Enum\AppointmentOnlineMessageTypeEnum;
use Modules\AppointmentUser\Enum\AppointmentOnlineStatusEnum;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DisabledAwnsweredOnlineAppointmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'appointment::closeOnlineAppointment';

    /**
     * The console command description.
     */
    protected $description = 'check for if appointment has awnswered , close them.';

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
        AppointmentOnline::whereHas('messages', function ($q) {
            $hasAwnswerd = $q->where('type', AppointmentOnlineMessageTypeEnum::ANSWER)
                ->whereNotNull('answer_by')
                ->where('created_at', '<=', \now()->subDays(2));
            if ($hasAwnswerd->exists()) {
                $hasAwnswerd->online()->update([
                    'status',
                    AppointmentOnlineStatusEnum::COMPLETED_BY_DOCTOR,
                    'close_at' => \now(),
                ]);
            }
        });
    }
}
