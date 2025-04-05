<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">تجویز رژیم برای <span class="text-secondary">{{ $user->fullName }}</span></h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">تجویز رژیم</li>
                <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">کاربران</a></li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="form-group" wire:ignore>
                        <label class="form-label mb-3">انتخاب رژیم</label>
                        <select wire:model='selectedDiet' id="select_2"
                            class="form-control select2-show-search form-select">
                            <option value="">انتخاب کنید</option>
                            @foreach (Modules\Diet\Entities\DietPlan::all() as $diet)
                                <option @if ($suggestedDiet == $diet->id) selected @endif value="{{ $diet->id }}">
                                    {{ $diet->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-muted mt-2 ms-2">پلن پیشنهادی به صورت پیشفرض انتخاب شده است</p>
                    </div>
                    @error('selectedDiet')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                    <div class="d-flex justify-content-end mt-5">
                        <button wire:click='assignDiet' class="btn btn-success">
                            <span wire:loading.remove>ذخیره</span>
                            <div wire:loading class="spinner-border spinner-border-sm mt-1" role="status">

                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>

    <script>
        document.addEventListener('livewire:initialized', () => {
            $('#select_2').select2();
            $('#select_2').on('change', function() {
                var selectedValue = $(this).val();
                @this.set('selectedDiet', selectedValue);
            });
        })
    </script>
@endpush
