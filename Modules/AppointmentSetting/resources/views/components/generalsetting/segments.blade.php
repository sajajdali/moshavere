<div class="card-header border-bottom d-flex justify-content-between  ">
    <h3 class="d-flex align-item-center">
        <i class="fa fa-compress me-2 d-none d-sm-inline" aria-hidden="true"></i>
        <span>
            زمان بندی و هزینه بخش ها
        </span>
    </h3>
    <h3></h3>
    <div class="main-toggle-group d-sm-flex align-items-center ms-0">
        <div class="toggle toggle-lg toggle-primary my-1 @if (isset($this->form['segments']['status']) && $this->form['segments']['status'] == true) on @else off @endif customCheckbox"
            wire:ignore.self data-bs-toggle="collapse" href="#sectionTimeTimeCollaps" role="button" aria-expanded="false"
            data-id="segments.status" aria-controls="sectionTimeTimeCollaps">
            <span></span>
        </div>
    </div>
</div>
<div class="card-body collapse @if (isset($this->form['segments']['status']) && $this->form['segments']['status'] == true) show @endif" id="sectionTimeTimeCollaps"
    wire:ignore.self>
    @if (isset($this->fetchData['segments']))
        <div class="row">
            <div class="form-group">
                <label class="form-label" for="segment_select_options">لطفا زمان بندی مرتبط را انتخاب کنید</label>
                <select wire:model='form.segments.value'
                    class="form-control form-select @error('form.segments.value') is-invalid @enderror"
                    id="segment_select_options" data-bs-placeholder="انتخاب کنید...">
                    <option value="">انتخاب کنید</option>
                    @foreach ($fetchData['segments'] as $segment)
                        <option @if (isset($form['segments']['value']) && $form['segments']['value'] == $segment->id) selected @endif value="{{ $segment->id }}">
                            {{ $segment->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @else
        <div class="row">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <span class="alert-inner--text">لطفا ابتدا از قسمت تنظیمات نوبت دهی و سپس بخش بندی نوبت ، یک بخش بندی به
                    سیستم اضافه بکنید.!</span>
                @can('crate', \Modules\AppointmentSetting\app\Models\AppointmentSegment::class)
                    <a href="{{ route('admin.appointment.segment.create') }}" class="btn btn-info" type="button">
                        اضافه کردن بخش بندی
                    </a>
                @endcan
            </div>
        </div>
    @endif
</div>
