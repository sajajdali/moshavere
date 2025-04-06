<?php

namespace App\Broadcasting;

use Modules\User\Entities\User;
use Illuminate\Support\Facades\Log;
use Modules\Setting\Enum\SettingKeyEnum;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user): array|bool
    {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function send($notifiable, Notification $notification): void
    {
        $data = $notification->toArray($notifiable);
        if (isset($data['template']) && !empty($data['template'])) {
            $smsSandbox = env('SMS_SEND_SANDBOX');
            $apiToken = setting(SettingKeyEnum::SMS_API_TOKEN);
            //data should have receptor and template and at least one params
            if ($smsSandbox !== true && $apiToken != '' && isset($data['receptor']) && isset($data['template']) && isset($data['params']) && count($data['params']) > 0) {
                $condition = [
                    'receptor' => $data['receptor'],
                    'template' => $data['template'],
                ];
                foreach ($data['params'] as $parameter) {
                    $condition['param'][] = $parameter;
                }
                try {
                    if(isset($data['type']) && $data['type'] == 2 ) {
                        \Illuminate\Support\Facades\Http::withToken($apiToken)->get('https://shsms.ir/api/v1/call', $condition);
                    }else{
                        \Illuminate\Support\Facades\Http::withToken($apiToken)->get('https://shsms.ir/api/v1/sendms', $condition);
                    }
                } catch (\Throwable $th) {
                    Log::error('shsms has issue:' .  $th->getMessage());
                }
            }
        }
    }
}
