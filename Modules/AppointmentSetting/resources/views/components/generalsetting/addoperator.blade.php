<div>
    <div class="card-header border-bottom d-flex justify-content-between">      
        <h3 class="d-flex align-item-center">
            <i class="fa fa-user-plus me-2 d-none d-sm-inline" aria-hidden="true"></i>
            <span>
                اضافه کردن اپراتور برای این بخش
            </span>
        </h3>
        <div class="main-toggle-group d-sm-flex align-items-center ms-0">
            <div class="toggle toggle-lg toggle-primary my-1 customCheckbox @if (!empty($form['operators'])) ) on @else off @endif"
                data-id="operators.status" wire:ignore.self data-bs-toggle="collapse" href="#addOpratorCollaps"
                role="button" aria-expanded="false" aria-controls="addOpratorCollaps">
                <span></span>
            </div>
        </div>
    </div>
    <div class="card-body collapse @if (!empty($form['operators'])) show @endif " id="addOpratorCollaps"
        wire:ignore.self>
        {{-- section --}}
        <div class="row">
            @error('form.operators.ids')
                <div class="alert alert-danger" role="alert">
                    <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i> لطفا حداقل یک اپراتور انتخاب
                    کنید یا این بخش را غیر فعال کنید!
                </div>
            @enderror
            <div class="col-md-12">
                <div class="form-group" wire:ignore>
                    @if (isset($fetchData['operator']) && $fetchData['operator']->isNotEmpty())
                        <label class="form-label">انتخاب اپراتور</label>
                        <select multiple class="form-control select2-show-search form-select" id="speciificDocSelect2"
                            data-placeholder="انتخاب کنید...">
                            <option label="انتخاب کنید..."></option>
                            @foreach ($fetchData['operator'] as $doctor)
                                <option @if (isset($form['operators']) && in_array($doctor->id, $form['operators'])) selected @endif value="{{ $doctor->id }}">
                                    {{ $doctor->fullname }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="col-md-12 alert alert-info fade show" role="alert">
                            لطفا برای انتخاب اپراتور ، از قسمت اضافه کردن کاربر، یک کاربر با نقش اپراتور به سیستم اضافه
                            کنید!
                            <a href="{{route('admin.user.create')}}" class="btn btn-primary">
                                <i class="fa fa-user-plus" aria-hidden="true"></i>
                                اضافه کردن کاربر
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
