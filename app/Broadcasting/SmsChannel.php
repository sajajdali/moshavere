<?php

namespace App\Broadcasting;

use DateTimeImmutable;
use Ghasedak\GhasedaksmsApi;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Modules\Setting\Enum\SettingKeyEnum;
use Illuminate\Notifications\Notification;
use Ghasedak\DataTransferObjects\Request\ReceptorDTO;
use Ghasedak\DataTransferObjects\Request\OtpMessageWithParamsDTO;

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
    public function join(User $user): array|bool {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function send($notifiable, Notification $notification): void
    {
        $settingCollection = \Modules\Setting\Entities\Setting::whereIn('setting_key', [
            SettingKeyEnum::SMS_SENDER,
            SettingKeyEnum::SMS_PARSSMS_SENDER_NUMBER,
            SettingKeyEnum::SMS_FARAZ_LINE_NUMBER,
            SettingKeyEnum::SMS_API_TOKEN,
            SettingKeyEnum::SMS_PARSSMS_LOGIN_TEXT,
            SettingKeyEnum::SMS_PARSSMS_ADD_APPOINTMENT_TEXT,
            SettingKeyEnum::SMS_PARSSMS_EDIT_APPOINTMENT_TEXT,
            SettingKeyEnum::SMS_PARSSMS_CANCEL_APPOINTMENT_TEXT,
            SettingKeyEnum::SMS_PARSSMS_REMINDER_TEXT,
            SettingKeyEnum::SMS_API_LOGIN_TEMPLATE,               //login
            SettingKeyEnum::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL, //get app
            SettingKeyEnum::SMS_APPOINTMENT_TIME_UPDATE,          //edit app
            SettingKeyEnum::SMS_APPOINTMENT_CANCEL,               //cancel app
            SettingKeyEnum::SMS_PRRSSMS_MONITORING_APP,
            SettingKeyEnum::SMS_PARSSMS_MONITORING_APPROVED_APP,
            SettingKeyEnum::SMS_PARSSMS_MONITORING_DIS_APPROVED_APP,
            SettingKeyEnum::SMS_PRSSMS_APPOINTMENT_WAITING_PAYMENT,
            SettingKeyEnum::SMS_SET_APP_MONITORING,
            SettingKeyEnum::SMS_APPROVED_MONITORING_APPOINTMENT,
            SettingKeyEnum::SMS_DIS_APPROVED_MONITORING_APPOINTMENT,
            SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT,

        ])->get();
        $apiToken     = $this->getSettingValue($settingCollection, SettingKeyEnum::SMS_API_TOKEN);
        $senderDriver = $this->getSettingValue($settingCollection, SettingKeyEnum::SMS_SENDER);
        $smsSandbox   = env('SMS_SEND_SANDBOX');
        $data         = $notification->toArray($notifiable);
        if ($senderDriver == 'parsasms') {
            $this->sendWithParsSms($apiToken, $data, $settingCollection);
            return;
        }
        if ($senderDriver == 'farazsms') {
            $this->sendWithFarrazSms($apiToken, $data, $settingCollection);
            return;
        }
        if ($senderDriver == 'ippannel') {
            $this->sendWithIpPanel($apiToken, $data, $settingCollection);
            return;
        }
        if ($senderDriver == 'starpayam') {
            $this->sendWithStarPayam($apiToken, $data, $settingCollection);
            return;
        }
        if (isset($data['template']) && !empty($data['template'])) {
            //data should have receptor and template and at least one params
            if (
                $smsSandbox !== true && $apiToken != ''
                && isset($data['receptor']) && isset($data['template']) && isset($data['params']) && count($data['params']) > 0
            ) {
                $condition = [
                    'receptor' => $data['receptor'],
                    'template' => $data['template'],
                ];
                foreach ($data['params'] as $parameter) {
                    $condition['param'][] = $parameter;
                }
                // if ($senderDriver !== null && $senderDriver == 'ghasedak') {
                //     $this->sendWithGhasedak($apiToken, $condition);
                //     return;
                // }
                try {
                    if (isset($data['type']) && $data['type'] == 2) {
                        \Illuminate\Support\Facades\Http::withToken($apiToken)->get('https://shsms.ir/api/v1/call', $condition);
                    } else {
                        \Illuminate\Support\Facades\Http::withToken($apiToken)->get('https://shsms.ir/api/v1/sendms', $condition);
                    }
                } catch (\Throwable $th) {
                    Log::error('shsms has issue:' .  $th->getMessage());
                } catch (\Exception $e) {
                    Log::error('shsms has issue:' .  $e->getMessage());
                }
            }
        }
    }
    private function sendWithStarPayam($apiToken, $data, $settings): void
    {
        $findSmsText =  $this->findSmsText($data['template'], $settings);
        $finalText = $this->paramToText($findSmsText, $data['params']);
        try {
            $payload = [
                'receptor' => $data['receptor'] ?? null,
                'sender'   => $this->getSettingValue($settings, SettingKeyEnum::SMS_FARAZ_LINE_NUMBER),
                'message'  => $finalText
            ];
            $response =  Http::withHeaders([
                'apikey' => $apiToken,
            ])->post('http://api.iransmsservice.com/v2/sms/send/simple', $payload);
            if (! $response->successful()) {
                Log::error('starPayam SMS failed', [
                    'payload' => $payload,
                    'response' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('starPayam Exception: ' . $e->getMessage());
        }
    }
    private function sendWithIpPanel($apiToken, $data, $settings): void
    {
        try {
            $variables = [];

            foreach (array_values($data['params'] ?? []) as $index => $value) {
                $variables['param' . ($index + 1)] = $value;
            }
            $payload = [
                'sending_type' => 'pattern',
                'from_number' => $this->getSettingValue($settings, SettingKeyEnum::SMS_FARAZ_LINE_NUMBER),
                'code' => $data['template'] ?? null,
                'recipients' => [
                    $data['receptor'] ?? null,
                ],
                'params' => $variables,
            ];

            $response = Http::withHeaders([
                'Authorization' => $apiToken,
                'Content-Type' => 'application/json',
            ])->post('https://edge.ippanel.com/v1/api/send', $payload);

            if (! $response->successful()) {
                Log::error('IPPANEL SMS failed', [
                    'payload' => $payload,
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('IPPANEL exception: ' . $e->getMessage());
        }
    }
    private function normalizeIpPanelMobile(?string $mobile): ?string
    {
        if (is_null($mobile)) {
            return null;
        }

        $mobile = trim($mobile);

        if (str_starts_with($mobile, '+')) {
            return $mobile;
        }

        $mobile = preg_replace('/\D/', '', $mobile);

        if (str_starts_with($mobile, '00')) {
            return '+' . substr($mobile, 2);
        }

        if (str_starts_with($mobile, '98')) {
            return '+' . $mobile;
        }

        if (str_starts_with($mobile, '0')) {
            return '+98' . substr($mobile, 1);
        }

        return '+98' . $mobile;
    }
    private function isValidIpPanelMobile(?string $mobile): bool
    {
        return is_string($mobile) && preg_match('/^\+98\d{10}$/', $mobile) === 1;
    }
    private function sendWithGhasedak($apiToken, $condition)
    {
        $args = [
            'sendDate' => new DateTimeImmutable('now'),
            'receptors' => [
                new ReceptorDTO(
                    mobile: $condition['receptor'],
                    clientReferenceId: '1'
                )
            ],
            'templateName' => $condition['template'],
        ];

        // Add dynamic params (param1, param2, ...)
        foreach ($condition['param'] as $index => $p) {
            $args['param' . ($index + 1)] = $p;
        }

        try {
            $ghasedaksms = new GhasedaksmsApi($apiToken);
            $ghasedaksms->sendOtpWithParams(new OtpMessageWithParamsDTO(...$args));
        } catch (\Throwable $th) {
            Log::error('shsms has issue: ' .  $th->getMessage());
        }
    }
    private function sendWithParsSms($apiToken, $data, $settings): void
    {
        try {
            $findSmsText =  $this->findSmsText($data['template'], $settings);
            $finalText = $this->paramToText($findSmsText, $data['params']);
            $r = \Illuminate\Support\Facades\Http::withHeader('apiKey', $apiToken)
                ->post('http://api.ghasedaksms.com/v2/sms/send/simple', [
                    'sender' => $this->getSettingValue($settings, SettingKeyEnum::SMS_PARSSMS_SENDER_NUMBER),
                    'message' => $finalText,
                    'receptor' => $data['receptor'],
                ]);
        } catch (\Exception $e) {
            Log::error('pars sms send error', [
                'error message' => $e->getMessage(),
                'error line' => $e->getTrace(),
            ]);
        }
        return;
    }
    private function sendWithFarrazSms($apiToken, $data, $settings)
    {
        try {
            $findSmsText =  $this->findSmsText($data['template'], $settings);
            $finalText = $this->paramToText($findSmsText, $data['params']);
            $r = \Illuminate\Support\Facades\Http::withHeader('apiKey', $apiToken)
                ->post('https://api.iranpayamak.com/ws/v1/sms/simple', [
                    'line_number'    => $this->getSettingValue($settings, SettingKeyEnum::SMS_FARAZ_LINE_NUMBER),
                    'text'           => $finalText,
                    'recipients'     => [$data['receptor']],
                ]);
        } catch (\Exception $e) {
            Log::error('farzSms send error', [
                'error message' => $e->getMessage(),
                'error line'    => $e->getTrace(),
            ]);
        }
        return;
    }
    private function getSettingValue($settingCollection, $settingKey)
    {
        return $settingCollection->firstWhere('setting_key', $settingKey)?->setting_value;
    }
    private function findSmsText($template, $settings)
    {
        return match ($template) {
            $this->getSettingValue($settings, SettingKeyEnum::SMS_API_LOGIN_TEMPLATE)                  => $this->getSettingValue($settings, SettingKeyEnum::SMS_PARSSMS_LOGIN_TEXT),
            $this->getSettingValue($settings, SettingKeyEnum::SMS_APPOINTMENT_RECEIVING_SUCCESSFUL)    => $this->getSettingValue($settings, SettingKeyEnum::SMS_PARSSMS_ADD_APPOINTMENT_TEXT),
            $this->getSettingValue($settings, SettingKeyEnum::SMS_APPOINTMENT_TIME_UPDATE)             => $this->getSettingValue($settings, SettingKeyEnum::SMS_PARSSMS_EDIT_APPOINTMENT_TEXT),
            $this->getSettingValue($settings, SettingKeyEnum::SMS_APPOINTMENT_CANCEL)                  => $this->getSettingValue($settings, SettingKeyEnum::SMS_PARSSMS_CANCEL_APPOINTMENT_TEXT),
            $this->getSettingValue($settings, SettingKeyEnum::SMS_SET_APP_MONITORING)                  => $this->getSettingValue($settings, SettingKeyEnum::SMS_PRRSSMS_MONITORING_APP),
            $this->getSettingValue($settings, SettingKeyEnum::SMS_APPROVED_MONITORING_APPOINTMENT)     => $this->getSettingValue($settings, SettingKeyEnum::SMS_PARSSMS_MONITORING_APPROVED_APP),
            $this->getSettingValue($settings, SettingKeyEnum::SMS_DIS_APPROVED_MONITORING_APPOINTMENT) => $this->getSettingValue($settings, SettingKeyEnum::SMS_PARSSMS_MONITORING_DIS_APPROVED_APP),
            $this->getSettingValue($settings, SettingKeyEnum::SMS_APPOINTMENT_WAITING_PAYMENT)         => $this->getSettingValue($settings, SettingKeyEnum::SMS_PRSSMS_APPOINTMENT_WAITING_PAYMENT),
            default => '',
        };
    }
    public function paramToText($msg, $params)
    {
        $ss                 = $this->changeSmsParameters($params);
        if (! is_null($msg)) {
            $final_mesg         = $this->computeSms($msg, $ss);
            return $final_mesg;
        }
        return '';
    }
    private function changeSmsParameters($params)
    {
        if (! is_array($params) || $params === []) {
            return $params;
        }
        $newParams = [];
        foreach ($params as $index => $param) {
            $newParams['%param' . ($index + 1) . '%'] = $param;
        }
        return $newParams;
    }
    private function computeSms(string $message, array $params)
    {
        if ($params === []) {
            return $message;
        }
        return strtr($message, $params);
    }
}
