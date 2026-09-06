<div class="oc-field">
    <label class="oc-label" for="{{ $name }}">{{ $label }}@if($required ?? false)<span class="oc-required" aria-hidden="true">*</span>@endif</label>
    @if(isset($options))
        <select class="oc-input" name="{{ $name }}" id="{{ $name }}" aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}" aria-describedby="{{ $name }}-hint" @required($required ?? false)>
            @foreach($options as $value => $text)
                <option value="{{ $value }}" @selected((string) old($name, $record->$name) === (string) $value)>{{ $text }}</option>
            @endforeach
        </select>
    @else
        <input class="oc-input" id="{{ $name }}" name="{{ $name }}" type="{{ $type ?? 'text' }}"
            value="{{ ($type ?? '') === 'password' ? '' : old($name, $record->$name) }}"
            dir="{{ $direction ?? (in_array($type ?? '', ['number', 'password']) ? 'ltr' : 'rtl') }}"
            aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}" aria-describedby="{{ $name }}-hint"
            @if(($type ?? '') === 'password') autocomplete="new-password" @endif
            @if(isset($min)) min="{{ $min }}" @endif @if(isset($max)) max="{{ $max }}" @endif
            @required($required ?? false)>
    @endif
    <div id="{{ $name }}-hint">
        @if(isset($help))<small class="oc-help">{{ $help }}</small>@endif
        @error($name)<small class="oc-error">{{ $message }}</small>@enderror
    </div>
</div>
