<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">
                گالری پزشک
            </h1>
        </div>
    </div>

    @include('admin::layouts.components.alert')
    @if ($message != 'false')
    <div class="col-md-12 alert alert-success fade show" role="alert">
        <i class="fa fa-remove me-2" aria-hidden="true"></i>
        {{ $message }}
    </div>
    @endif
    <div class="row" >
        <div class="col-md-12">
            <div class="card ">
                @error('*')
                    {{ $message }}
                @enderror
                <div class="card-header justify-content-between border-bottom">
                    <h3 class="card-title ">افزودن تصویر</h3>
                    <button wire:click='addItem' wire:target='addItem' wire:loading.class='btn-loading btn-gray'
                        class="btn btn-info">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        اضافه کردن تصویر بیشتر
                    </button>
                </div>
                <div class="card-body">
                    @for ($i = 0; $i < $form['count_items']; $i++)
                        <div class='row mb-5'>
                            <div class="col-md-1 mb-2 mb-md-0">
                                <span class="badge bg-secondary">
                                    {{ $i + 1 }}
                                </span>
                            </div>
                            <div class="col-md-9 mb-2 mb-md-0">
                                <div class="input-group ">
                                    <span class="input-group-btn">
                                        <button data-index = "{{ $i }}" class="btn btn-primary select_file"
                                            data-bs-target="#file-selector-modal" data-bs-toggle="modal" type="button">
                                            <i class="fa fa-picture-o"></i>
                                            انتخاب تصویر
                                        </button>
                                    </span>
                                    <input id="thumbnail-{{ $i }}"
                                        class="form-control @error('form.items.' . $i) is-invalid @enderror"
                                        type="text" name="filepath" wire:model="form.items.{{ $i }}">
                                </div>
                                @error('form.items.' . $i)
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @if ($i != 0)
                                <div class="col-md-2 mb-2 mb-md-0">
                                    <button class="btn btn-danger " wire:target='removeCounter({{ $i }})'
                                        wire:loading.class='btn-loading'
                                        wire:click='removeCounter({{ $i }})'>
                                        <i class="fa fa-minus" aria-hidden="true"></i>
                                    </button>
                                </div>
                            @endif

                        </div>
                    @endfor
                    <div class="row justify-content-end">
                        <div class="col-2">
                            <button wire:click='storeGalleryiespics' wire:target='storeGalleryiespics'
                                wire:loading.class='btn-loading btn-gray' class="btn btn-success">ذخیره ی
                                تصاویر</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @if (isset($fetchData['items']))
            <div class="col-md-12 row">
                @foreach ($fetchData['items'] as $key => $image)
                    <div class="col-md-3"  >
                        <div class="card ">
                            <div class="card-header ">
                                <button class="btn-danger btn" type="button" wire:click='removePick({{ $key }})'
                                    wire:loading.class='btn-loading' wire:target='removePick({{ $key }})'> حذف
                                    <i class="fe fe-x "></i></button>
                            </div>
                            <div class="card-body text-center">
                                <img src="{{ $image }}" alt="">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        @endif
    </div>
    <livewire:admin::file-manager-modal />
</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            var index = 0;
            $('body').on('click', '.select_file', function() {
                index = $(this).data('index');
            });
            Livewire.on('select_file', (param) => {
                @this.set('form.items.' + index, param.url);
                //close modal
                $('#file-selector-modal').modal('hide');
            });
        });
    </script>
@endpush
