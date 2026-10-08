{{-- $totals: net, system, manual, refund, rows, patients --}}
<div class="fin-kpis">
    <div class="fin-kpi">
        <span class="fin-kpi-icon blue"><i class="fa fa-money" aria-hidden="true"></i></span>
        <div>
            <div class="fin-kpi-value">{{ number_format($totals->net) }}<span class="fin-kpi-unit">تومان</span></div>
            <div class="fin-kpi-label">خالص دریافتی</div>
        </div>
    </div>
    <div class="fin-kpi">
        <span class="fin-kpi-icon green"><i class="fa fa-globe" aria-hidden="true"></i></span>
        <div>
            <div class="fin-kpi-value">{{ number_format($totals->system) }}<span class="fin-kpi-unit">تومان</span></div>
            <div class="fin-kpi-label">پرداخت های سیستمی</div>
        </div>
    </div>
    <div class="fin-kpi">
        <span class="fin-kpi-icon amber"><i class="fa fa-hand-paper-o" aria-hidden="true"></i></span>
        <div>
            <div class="fin-kpi-value">{{ number_format($totals->manual) }}<span class="fin-kpi-unit">تومان</span></div>
            <div class="fin-kpi-label">پرداخت های ثبت دستی</div>
        </div>
    </div>
    <div class="fin-kpi">
        <span class="fin-kpi-icon red"><i class="fa fa-undo" aria-hidden="true"></i></span>
        <div>
            <div class="fin-kpi-value">{{ number_format($totals->refund) }}<span class="fin-kpi-unit">تومان</span></div>
            <div class="fin-kpi-label">بازگشت وجه</div>
        </div>
    </div>
    <div class="fin-kpi">
        <span class="fin-kpi-icon gray"><i class="fa fa-users" aria-hidden="true"></i></span>
        <div>
            <div class="fin-kpi-value">{{ number_format($totals->patients) }}<span class="fin-kpi-unit">بیمار</span></div>
            <div class="fin-kpi-label">{{ number_format($totals->rows) }} پرداخت</div>
        </div>
    </div>
</div>
