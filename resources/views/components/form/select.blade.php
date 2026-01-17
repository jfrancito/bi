<div class="col-lg-{{ $col }}">
	<div class="form-group mb-4">
		<label class="label text-secondary">{{ $label }}</label>
		<div class="form-group position-relative">
			
			<select 
				name="{{ $name }}" 
				id="{{ $id ?? 'selectcampo' }}"
				class="form-select form-control ps-5 h-55 custom-select-{{ $name }}"
				data-placeholder="Seleccione una opción"
				{{ $required ?? '' }}
			>
				@foreach($options as $key => $text)
					<option 
						value="{{ $key }}" 
						{{ $value == $key ? 'selected' : '' }}
					>
						{{ $text }}
					</option>
				@endforeach
			</select>
			@if($icon)
				<i class="{{ $icon }} position-absolute top-50 start-0 translate-middle-y fs-20 ps-20"></i>
			@endif
		</div>
	</div>
</div>

{{-- <div class="col-lg-{{ $col ?? 6 }}">
    <div class="form-group mb-4">
        <label class="label text-secondary">{{ $label }}</label>

        <div class="form-group position-relative">
            <select 
                name="{{ $name }}"
                class="form-select form-control ps-5 h-55 {{ $class ?? '' }}"
            >
                @foreach($options as $key => $text)
                    <option value="{{ $key }}" {{ ($value == $key) ? 'selected' : '' }}>
                        {{ $text }}
                    </option>
                @endforeach
            </select>

            @if($icon)
                <i class="{{ $icon }} position-absolute top-50 start-0 translate-middle-y fs-20 ps-20"></i>
            @endif
        </div>
    </div>
</div> --}}

{{-- 	<div class="col-lg-{{ $col }}">
		<div class="form-group mb-4">
			<label class="label text-secondary">{{ $label }}</label>
			<div class="form-group position-relative">
				<select 
					name="{{ $name }}" 
					class="form-select form-control ps-5 h-55 select2-input text-dark input-lg"
				>
					@foreach($options as $key => $text)
						<option 
							value="{{ $key }}" 
							{{ $value == $key ? 'selected' : '' }}
						>
							{{ $text }}
						</option>
					@endforeach
				</select>
			</div>
		</div>
	</div>
 --}}