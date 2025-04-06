<div>
    <div class="my-5">
        <div class="form-check" wire:click='Checkboxvalue'>
            <input class="form-check-input" type="checkbox"  wire:model="checkboxvalue" name="check" @if ( isset($this->old_value) && $this->old_value == 1 ) checked @endif
                id="check_box_{{$meta->name}}">
            <label class="form-check-label" for="check_box_{{$meta->name}}">
              <strong> {{ $meta->getName() }}</strong>
            </label>
        </div>
        @if($meta->getDescription())
        <blockquote>
            {!!  $meta->getDescription() !!}
        </blockquote>
        <hr>
    @endif
    </div>
</div>
