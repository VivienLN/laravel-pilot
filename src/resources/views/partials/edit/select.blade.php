    <label class="col-lg-2 col-form-label" for="form_{{ $prop }}">{{ $display }}</label>
    <div class="col-lg-10">
        <select name="{{ $prop }}" id="form_{{ $prop }}" class="form-control" @if($readonly) disabled @endif>
            <option value=""></option>
            @foreach($params as $option)
                <option value="{{ $option }}" @if($value == $option) selected @endif >{{ $option }}</option>
            @endforeach
        </select>
    </div>