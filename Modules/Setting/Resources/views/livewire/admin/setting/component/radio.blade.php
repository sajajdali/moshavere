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
    <div class="example mb-5">
        <p class="text-muted">{{ $meta->getName() }}</p>
        @foreach($meta->options() as $key => $option)
            <div class="form-check">
                <input class="form-check-input" type="radio" value="{{ $key }}"
                       wire:model="radioValue"
                       name="radio_{{ $meta->value }}"
                       id="radio_{{ $meta->value }}_{{ $key }}">
                <label class="form-check-label" for="radio_{{ $meta->value }}_{{ $key }}">
                    {{ $option }}
                </label>
            </div>
        @endforeach
    </div>
</div>
