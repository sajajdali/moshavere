@props([
    'type' => 'text',        // Field type: input, textarea, select, password, radio, checkbox
    'id' => '',              // Field ID
    'name',                  // Field name
    'label' => null,         // Field label
    'placeholder' => '',     // Placeholder text
    'options' => [],         // Options for select fields, radio buttons, or checkboxes
    'dynamicOptions' => null, // Dynamic options for select or checkboxes (e.g., fetched from the server)
    'value' => '',           // Default value
    'model' => '',           // Support for wire:model
    'showToggle' => false,   // Whether to show password visibility toggle
    'const_group' => null,
    'class' => null,
    'disabled' => false,
    'description' => null
])

@if ($label)
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
@endif
<div class="input-group">

    <!-- Field types -->
    @if ($type === 'textarea')
        <textarea id="{{ $id }}"
                  name="{{ $name }}"
                  placeholder="{{ $placeholder }}"
                  {{ $attributes->merge(['class' => 'form-control' . ($errors->has($name) ? ' is-invalid' : '')]) }}
                  wire:model="{{ $model }}">{{ old($name, $value) }}</textarea>

    @elseif ($type === 'select')
        <select id="{{ $id }}"
                name="{{ $name }}"
                {{ $attributes->merge(['class' => 'form-select' . ($errors->has($name) ? ' is-invalid' : '')]) }}
                wire:model="{{ $model }}">
            @if($placeholder)<option value="">{{ $placeholder }}</option>@endif

            <!-- Dynamic options from a model -->
            @if ($dynamicOptions)
                @foreach ($dynamicOptions as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            @endif

            <!-- Static options -->
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" {{ old($name, $value) == $optionValue ? 'selected' : '' }}>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>

    @elseif ($type === 'radio')
        <div class="d-flex align-items-center">
            @foreach ($options as $optionValue => $optionLabel)
                <div class="form-check me-3">
                    <input type="radio"
                           id="{{ $id . '_' . $optionValue }}"
                           name="{{ $name }}"
                           value="{{ $optionValue }}"
                           {{ $attributes->merge(['class' => 'form-check-input' . ($errors->has($name) ? ' is-invalid' : '')]) }}
                           wire:model="{{ $model }}">
                    <label for="{{ $id . '_' . $optionValue }}" class="form-check-label">{{ $optionLabel }}</label>
                </div>
            @endforeach
        </div>

    @elseif ($type === 'checkbox')
        <div class="d-flex flex-column">
            @foreach ($options as $option)
                <div class="form-check">
                    <input type="checkbox"
                           id="{{ $id . '_' . $option['value'] }}"
                           name="{{ $name }}[]"
                           value="{{ $option['value'] }}"
                           {{ $attributes->merge(['class' => 'form-check-input' . ($errors->has($name) ? ' is-invalid' : '')]) }}
                           wire:model="{{ $model }}">
                    <label for="{{ $id . '_' . $option['value'] }}" class="form-check-label">
                        {{ $option['label'] }}
                    </label>
                </div>
            @endforeach
        </div>

    @else
        <input type="{{ $type }}"
               id="{{ $id }}"
               @if($disabled) disabled="disabled" @endif
               name="{{ $name }}"
               placeholder="{{ $placeholder }}"
               {{ $attributes->merge(['class' => 'form-control' . ($errors->has($name) ? ' is-invalid' : '') . (isset($class) ? " ".$class : '')]) }}
               wire:model="{{ $model }}"
               value="{{ old($name, $value) }}">
    @endif

    @if(isset($const_group))
        <span class="input-group-text">{{$const_group}}</span>
    @endif
</div>
@if(isset($description))
    <div class="form-text" id="defaultFormControlHelp">{{$description}}</div>
@endif

<!-- Error Message -->
@error($name)
<div class="text-danger mt-2">{{ $message }}</div>
@enderror
