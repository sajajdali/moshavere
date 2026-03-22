<div>
    @if ($meta->separatorTitle())
        <div class="row">
            <div class="col-md-5 fw-bold fs-6 mb-2 d-flex align-items-center text-dark">
                <i class="fa fa-cogs me-1" aria-hidden="true"></i>
                <span> {{ $meta->separatorTitle() }} </span>
            </div>
            <div class="col-md-7">
                <hr>
            </div>
        </div>
    @endif
    <div class="my-5">
        <div class="form-check" wire:click='Checkboxvalue'>
            <input class="form-check-input" type="checkbox" wire:model="checkboxvalue" name="check"
                @if ($meta->deactiveFeature()) disabled @endif @if (isset($this->old_value) && $this->old_value == 1) checked @endif
                id="check_box_{{ $meta->name }}">
            <label class="form-check-label" for="check_box_{{ $meta->name }}">
                <strong> {{ $meta->getName() }}</strong>
            </label>
        </div>
        @if ($meta->getDescription())
            <blockquote>
                {!! $meta->getDescription() !!}
            </blockquote>
            <hr>
        @endif
    </div>
</div>
