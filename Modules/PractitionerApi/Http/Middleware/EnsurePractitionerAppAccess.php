<?php

namespace Modules\PractitionerApi\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;
use Symfony\Component\HttpFoundation\Response;

class EnsurePractitionerAppAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->forbidden('Unauthenticated practitioner.');
        }

        if (! $user->tokenCan(config('practitionerapi.token_ability', 'practitioner-app'))) {
            return $this->forbidden('توکن اجازه دسترسی به اپلیکیشن پزشکان را ندارد.');
        }

        $testLoginEnabled = (bool) ConsultationSetting::current()->test_login_enabled;
        $practitioner = ConsultationPractitioner::query()
            ->where('user_id', $user->id)
            ->where('active', true)
            ->when(! $testLoginEnabled, fn ($query) => $query->where('app_access', true))
            ->first();

        if (! $practitioner) {
            return $this->forbidden('دسترسی اپلیکیشن برای این پزشک یا مشاور فعال نیست.');
        }

        $request->attributes->set('practitioner', $practitioner);

        return $next($request);
    }

    private function forbidden(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => [],
        ], Response::HTTP_FORBIDDEN);
    }
}
