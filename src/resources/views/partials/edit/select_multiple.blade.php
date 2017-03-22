    <label class="col-lg-2 col-form-label" for="form_{{ $prop }}">{{ $display }}</label>
	<div class="col-lg-10">
		<select name="{{ $prop }}[]" id="form_{{ $prop }}" class="form-control" multiple @if($readonly) disabled @endif>
			@foreach($params as $option)
				<option value="{{ $option }}">{{ $option }}</option>
			@endforeach
		</select>
	</div>