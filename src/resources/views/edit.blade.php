@extends('pilot::layout')

@section('content')
    <?php
        $action = url(sprintf('%s/%s/edit', config('pilot.prefix'), $slug));
        if(!empty($model)) {
            $action .= '/'.$model->getKey();
        }
    ?>
    <h2>{{ $title }}</h2>
    <form action="{{ $action }}" method="post">
        {{ csrf_field() }}
        @foreach($reflector['columns'] as $prop => $col)
            <?php
                $inputView = $reflector->getFormView($model, $prop, $canSave);
                $hasErrors = $errors && count($errors->get($prop)) > 0;

//                $value = old($prop) ?? $model->$prop ?? '';
//                $inputView = $reflector->getFormView($col['form'], [
//                    'prop' => $prop,
//                    'col' => $col,
//                    'value' => $value,
//                    'readonly' => !$col['editable'] || !$canSave,
//                ]);
            ?>
            <div class="form-group row @if($hasErrors) has-danger @endif">
                {!! $inputView->render() !!}
                @foreach($errors->get($prop) as $message)
                    <div class="form-control-feedback">{{ $message }}</div>
                @endforeach
            </div>
        @endforeach

        @if ($canSave)
            <button type="submit" class="btn btn-primary btn-block btn-lg btn-submit-tip">Save</button>
        @else
            <div class="alert alert-warning alert-dismissible">
                <strong>Warning:</strong> You don't have the permission to save this item.
            </div>
        @endunless
    </form>
@endsection