<div>
    <div class="card-header border-bottom d-flex justify-content-between">
        <h3> اضافه کردن اپراتور برای این بخش </h3>
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
                    <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i> لطفا حداقل یک اپراتور  انتخاب
                    کنید یا این بخش را غیر فعال کنید!
                </div>
            @enderror
            <div class="col-md-12">
                <div class="form-group" wire:ignore>
                    <label class="form-label">انتخاب اپراتور</label>
                    <select multiple class="form-control select2-show-search form-select" id="speciificDocSelect2"
                        data-placeholder="انتخاب کنید...">
                        <option label="انتخاب کنید..."></option>
                        @if (isset($fetchData['operator']))
                            @foreach ($fetchData['operator'] as $doctor)
                                <option @if (isset($form['operators']) && in_array($doctor->id, $form['operators'])) selected @endif value="{{ $doctor->id }}">
                                    {{ $doctor->fullname }}</option>
                            @endforeach
                        @else
                            <option value="null" disabled>لطفا ابتدا اپراتور به سیستم اضافه کنید!!</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
