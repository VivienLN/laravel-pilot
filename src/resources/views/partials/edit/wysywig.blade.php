    <label class="col-lg-2 col-form-label" for="form_{{ $prop }}">{{ $display }}</label>
    <div class="col-lg-10">
        <textarea rows="10" id="form_{{ $prop }}" name="{{ $prop }}" class="wysywig form-control"@if($readonly) disabled @endif>{{ $value }}</textarea>
    </div>
