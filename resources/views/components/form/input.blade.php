@php
	$hasError = $errors->has($name);
@endphp

<div class="col-lg-{{ $col??'6' }}">
	<div class="form-group mb-4">
		<label class="label text-secondary">{{ $label ?? 'Campo' }}</label>
		<div class="form-group position-relative">
			<input 
				type="{{ $type?? 'text' }}" 
				id="{{ $name ?? 'campo' }}" 
				name="{{ $name ?? 'campo' }}" 
				class="form-control text-dark ps-5 h-55"
				value="{{ $value ?? '' }}"
				placeholder="{{ $placeholder ?? 'Ingrese el campo' }}"
				{{ $disabled ?? '' }}
				{{ $required ?? '' }}
			>
			<i class="ri-user-line position-absolute top-50 start-0 translate-middle-y fs-20 ps-20"></i>

			@if($hasError)
				<div class="invalid-feedback d-block">
					{{ $errors->first($name) }}
				</div>
			@endif
		
		</div>
	</div>
</div>