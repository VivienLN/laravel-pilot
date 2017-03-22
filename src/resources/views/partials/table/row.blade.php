<?php use VivienLN\Pilot\Pilot; ?>
<tr>
    @foreach($columns as $prop => $col)
        <td>{!! $reflector->filter($item, $prop) !!}</td>
    @endforeach
    <td>
        <a href="{{ url(sprintf('%s/%s/edit/%s', config('pilot.prefix'), $slug, $item->id)) }}" class="btn btn-primary">
            @can('update', $item) {!! $pilot->getIcon('edit') !!} Edit @endcan
            @cannot('update', $item) {!! $pilot->getIcon('eye') !!} View @endcannot
        </a>
    </td>
</tr>