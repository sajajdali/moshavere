<?php

namespace Modules\PractitionerApi\Services;

use Laravel\Sanctum\PersonalAccessToken;
use Modules\Api\Entities\UserDevice;
use Modules\User\Entities\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PractitionerDeviceService
{
    public function update(User $user, array $data): array
    {
        $token = $user->currentAccessToken();
        $tokenId = $token instanceof PersonalAccessToken ? $token->getKey() : null;
        $query = UserDevice::query()->where('user_id', $user->id)
            ->where('device_info->device_identifier', $data['device_identifier']);
        if ($tokenId) $query->where('access_token_id', $tokenId);
        $device = $query->latest('id')->first();
        if (! $device) throw new NotFoundHttpException('دستگاه جاری برای این حساب یافت نشد؛ دوباره وارد شوید.');

        $device->update([
            'fcm_token' => filled($data['fcm_token']) ? trim($data['fcm_token']) : null,
            'device_version' => $data['device_version'] ?? $device->device_version,
        ]);

        return [
            'device_identifier' => data_get($device->device_info, 'device_identifier'),
            'notifications_enabled' => filled($device->fcm_token),
            'device_version' => $device->device_version,
            'updated_at' => $device->updated_at?->toIso8601String(),
        ];
    }
}
