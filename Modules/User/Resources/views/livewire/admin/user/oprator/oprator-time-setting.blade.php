<div>
    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">
                <span> تنظیمات زمان های حضور اپراتور</span>
                <strong class="text-primary">{{ $fetchData['oprator']->fullName }}</strong>
            </h1>
        </div>
    </div>
    @include('admin::layouts.components.alert')

    <div class="card">
        @include('appointmentsetting::components.generalsetting.dayofperesent')
    </div>
    <div class="row">
        <div class="col-12 text-end mb-5">
            <button class="btn btn-info"
             wire:click='storeTimes'
             wire:loading.class='btn-loading bg-gray'
             >ثبت و ذخیره اطلاعات</button>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        $(document).ready(function () {
            $('.customCheckbox').on('click', function() {
                var id = $(this).data('id');
                var inp =  $(this) ;
                @this.set('form.' + id, $(this).hasClass('on'));
                ChangePricesDisplay(id,inp);
            });
        });
    </script>
@endpush
