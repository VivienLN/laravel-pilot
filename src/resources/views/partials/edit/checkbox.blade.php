    <div class="col-lg-10 offset-lg-2">
        <label class="custom-control custom-checkbox">
            <?php $checked = (bool)$value; ?>
            <input type="checkbox" class="custom-control-input" name="{{ $prop }}" value="1" @if($checked) checked @endif @if($readonly) disabled @endif />
            <span class="custom-control-indicator"></span>
            <span class="custom-control-description">{{ $display }}</span>
        </label>
    </div>
