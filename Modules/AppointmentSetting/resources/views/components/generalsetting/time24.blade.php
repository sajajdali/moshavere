{{-- ورودی ساعت ۲۴ ساعته (بدون AM/PM). مقدار با قالب HH:MM در $model ذخیره میشود. --}}
@php($inputId = $id ?? 'time24-' . md5($model))
<div x-data="{
        v: $wire.entangle('{{ $model }}'),
        t: '',
        init() {
            this.show(this.v);
            this.$watch('v', (val) => this.show(val));
        },
        show(val) {
            const p = String(val ?? '').match(/^(\d{1,2}):(\d{2})/);
            this.t = p ? p[1].padStart(2, '0') + ':' + p[2] : '';
        },
        valid(h, m) {
            return h !== '' && m !== '' && +h <= 23 && +m <= 59;
        },
        typing() {
            const fa = '۰۱۲۳۴۵۶۷۸۹', ar = '٠١٢٣٤٥٦٧٨٩';
            const d = this.t
                .replace(/[۰-۹]/g, (c) => fa.indexOf(c))
                .replace(/[٠-٩]/g, (c) => ar.indexOf(c))
                .replace(/\D/g, '')
                .slice(0, 4);
            this.t = d.length > 2 ? d.slice(0, 2) + ':' + d.slice(2) : d;
            if (d === '') {
                this.v = '';
            } else if (d.length === 4 && this.valid(d.slice(0, 2), d.slice(2))) {
                this.v = this.t;
            }
        },
        done() {
            const d = this.t.replace(/\D/g, '');
            if (d === '') {
                this.v = '';
                return;
            }
            let h, m;
            if (d.length <= 2) {
                h = d.padStart(2, '0');
                m = '00';
            } else {
                h = d.slice(0, d.length - 2).padStart(2, '0');
                m = d.slice(-2);
            }
            if (this.valid(h, m)) {
                this.v = h + ':' + m;
            }
            this.show(this.v);
        },
    }">
    <input type="text" class="form-control" id="{{ $inputId }}" dir="ltr" inputmode="numeric" maxlength="5"
        placeholder="HH:MM" autocomplete="off" x-model="t" x-on:input="typing()" x-on:blur="done()">
</div>
