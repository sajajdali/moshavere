<div>
    @if ($meta->separatorTitle())
    <div class="row mt-3 mb-2">
            <div class="col-md-5 fw-bold fs-6 mb-2 d-flex align-items-center text-Dark">
                <i class="fa fa-cogs me-1" aria-hidden="true"></i>
                <span> {{ $meta->separatorTitle() }} </span>
            </div>
            <div class="col-md-7">
                <hr>
            </div>
        </div>
    @endif
    <div class="form-group">
        <label for="text_{{ $meta->value }}">{{ $meta->getName() }}</label>
        <input type="text" class="form-control disable_button_150" id="text_{{ $meta->value }}" placeholder="مقدار..."
            wire:model.live="textValue">
    </div>
    @if ($meta->getDescription())
        <blockquote>
            {!! $meta->getDescription() !!}
        </blockquote>
        <hr>
    @endif
</div>
