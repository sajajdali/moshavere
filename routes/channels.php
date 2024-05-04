<?php

use Illuminate\Support\Facades\Broadcast;
use Modules\User\Entities\User;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('chat.{chatId}', function (User $user, int $chatId) {
    return $user->id === \Modules\Chat\app\Models\Chat::find($chatId)?->user_id;
});
