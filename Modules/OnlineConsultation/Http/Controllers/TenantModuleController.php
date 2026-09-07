<?php

namespace Modules\OnlineConsultation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\OnlineConsultation\Support\ConsultationAccess;

class TenantModuleController extends Controller
{
    public function index()
    {
        return view('onlineconsultation::central', ['tenants' => Tenant::with('domains')->orderBy('id')->paginate(20)]);
    }

    public function update(Request $request, string $tenantId)
    {
        $data = $request->validate(['enabled' => ['required', 'boolean']]);
        $tenant = Tenant::findOrFail($tenantId);
        if ($data['enabled']) {
            $ready = $tenant->run(fn () => ConsultationAccess::schemaReady());
            if (! $ready) {
                throw ValidationException::withMessages([
                    'enabled' => 'جدول‌های مشاوره برای این سایت نصب نشده‌اند. ابتدا مهاجرت‌های ماژول را برای سایت اجرا کنید.',
                ]);
            }
        }
        $tenant->online_consultation_enabled = (bool) $data['enabled'];
        $tenant->save();

        return back()->with('success', 'وضعیت ماژول برای این سایت ذخیره شد.');
    }
}
