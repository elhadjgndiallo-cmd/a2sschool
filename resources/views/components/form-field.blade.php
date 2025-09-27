@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'help' => '',
    'options' => [],
    'colSize' => 'col-12 col-md-6',
    'disabled' => false,
    'readonly' => false,
    'min' => '',
    'max' => '',
    'step' => '',
    'accept' => '',
    'multiple' => false
])

<div class="{{ $colSize }} mb-3">
    @if($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
    
    @if($type === 'select')
        <select 
            name="{{ $name }}" 
            id="{{ $name }}" 
            class="form-select @error($name) is-invalid @enderror"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $multiple ? 'multiple' : '' }}
        >
            @if(!$required)
                <option value="">-- Sélectionner --</option>
            @endif
            @foreach($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" 
                    {{ old($name, $value) == $optionValue ? 'selected' : '' }}>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    @elseif($type === 'textarea')
        <textarea 
            name="{{ $name }}" 
            id="{{ $name }}" 
            class="form-control @error($name) is-invalid @enderror"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            rows="3"
        >{{ old($name, $value) }}</textarea>
    @elseif($type === 'file')
        <input 
            type="file" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            class="form-control @error($name) is-invalid @enderror"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $accept ? 'accept=' . $accept : '' }}
            {{ $multiple ? 'multiple' : '' }}
        >
    @else
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            class="form-control @error($name) is-invalid @enderror"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $min ? 'min=' . $min : '' }}
            {{ $max ? 'max=' . $max : '' }}
            {{ $step ? 'step=' . $step : '' }}
        >
    @endif
    
    @if($help)
        <div class="form-text">{{ $help }}</div>
    @endif
    
    @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>
