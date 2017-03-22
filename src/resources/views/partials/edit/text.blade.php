    <label class="col-lg-2 col-form-label" for="form_{{ $prop }}">{{ $display }}</label>
    <div class="col-lg-10">
        <input type="text" class="form-control" id="form_{{ $prop }}" name="{{ $prop }}" value="{{ $value }}" @if($readonly) disabled @endif />
    </div>