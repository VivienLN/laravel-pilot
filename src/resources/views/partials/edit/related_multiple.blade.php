    <label class="col-lg-2 col-form-label" for="form_{{ $prop }}">{{ $display }}</label>
	<div class="col-lg-10">
		<select name="{{ $prop }}[]" id="form_{{ $prop }}" class="form-control" @if($readonly) disabled @endif multiple>
			<option value=""></option>
			@foreach($params as $id => $item)
				<option value="{{ $id }}" @if(in_array($id, $value)) selected @endif>{{ $item }}</option>
			@endforeach
		</select>
	</div>