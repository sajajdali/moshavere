<div class="oc-field">
    <input type="hidden" name="{{ $name }}" value="0">
    <label class="oc-check" for="{{ $name }}">
        <input class="oc-check-input" type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1" @checked(old($name, $record->$name))>
        <span><span class="oc-check-title">{{ $label }}</span>@if(isset($help))<small class="oc-help">{{ $help }}</small>@endif</span>
    </label>
    @error($name)<small class="oc-error">{{ $message }}</small>@enderror
</div>
