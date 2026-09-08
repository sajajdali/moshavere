<?php

namespace App\Http\Middleware;

use App\Models\Domain;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceTenantSupportPeriod
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('site-disabled') || in_array($request->getHost(), config('tenancy.central_domains', []), true)) {
            return $next($request);
        }

        $tenantId = Domain::query()->where('domain', $request->getHost())->value('tenant_id');
        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        if ($tenant?->disabled) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $tenant->disabled_message ?: 'این سایت موقتاً غیرفعال شده است.',
                ], 403);
            }

            return redirect()->to('/site-disabled');
        }

        if ($this->isSupportExempt($request)) {
            return $next($request);
        }

        if (! $tenant?->expires_at || ! $tenant->expires_at->isPast()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'پشتیبانی این سایت به پایان رسیده است. ابتدا پشتیبانی را تمدید کنید.',
                'expired_at' => $tenant->expires_at->toIso8601String(),
            ], 402);
        }

        return redirect()->to('/support-expired');
    }

    private function isSupportExempt(Request $request): bool
    {
        if ($request->is('support-expired', 'shemiranWebLogin', 'admin/tenant/renew', 'admin/tenant/renew/callback')) {
            return true;
        }

        if (! $request->is('livewire/update')) {
            return false;
        }

        foreach ((array) $request->input('components', []) as $component) {
            $snapshot = json_decode($component['snapshot'] ?? '', true);
            if (data_get($snapshot, 'memo.name') === 'admin::tenant-renew') {
                return true;
            }
        }

        return false;
    }
}
