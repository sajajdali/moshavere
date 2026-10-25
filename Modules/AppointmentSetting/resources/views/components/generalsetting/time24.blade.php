{{-- ورودی ساعت ۲۴ ساعته (بدون AM/PM). مقدار با قالب HH:MM مستقیم با wire:model در $model ذخیره میشود. --}}
@php($inputId = $id ?? 'time24-' . md5($model))
<input type="text" class="form-control" id="{{ $inputId }}" wire:model="{{ $model }}" dir="ltr"
    inputmode="numeric" placeholder="HH:MM" autocomplete="off"
    x-data="{
        clean(s) {
            const fa = '۰۱۲۳۴۵۶۷۸۹', ar = '٠١٢٣٤٥٦٧٨٩';
            return String(s)
                .replace(/[۰-۹]/g, (c) => fa.indexOf(c))
                .replace(/[٠-٩]/g, (c) => ar.indexOf(c))
                .replace(/\D/g, '')
                .slice(0, 4);
        },
        mask(el) {
            const d = this.clean(el.value);
            el.value = d.length > 2 ? d.slice(0, 2) + ':' + d.slice(2) : d;
        },
        done(el) {
            const d = this.clean(el.value);
            let out = '';
            if (d !== '') {
                const h = (d.length <= 2 ? d : d.slice(0, -2)).padStart(2, '0');
                const m = d.length <= 2 ? '00' : d.slice(-2);
                out = (+h <= 23 && +m <= 59) ? h + ':' + m : (el.dataset.last || '');
            }
            el.value = out;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        },
    }"
    x-on:focus="$el.dataset.last = $el.value"
    x-on:input="mask($el)"
    x-on:blur="done($el)">
