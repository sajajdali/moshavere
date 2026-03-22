<div>
    @if ($meta->separatorTitle())
        <div class="row mt-3 mb-2">
            <div class="col-md-5 fw-bold fs-6 mb-2 d-flex align-items-center text-dark">
                <i class="fa fa-cogs me-1" aria-hidden="true"></i>
                <span> {{ $meta->separatorTitle() }} </span>
            </div>
            <div class="col-md-7">
                <hr>
            </div>
        </div>
    @endif
    <div class="form-group">
        <label for="select_{{ $meta->value }}">{{ $meta->getName() }}</label>
        <select class="form-select form-input" wire:model.live="selectValue" name="select_{{ $meta->value }}"
            id="select_{{ $meta->value }}">
            <option value="">انتخاب کنید</option>
            @foreach ($meta->options() as $key => $title)
                <option value="{{ $key }}">{{ $title }}</option>
            @endforeach
        </select>
    </div>
</div>
