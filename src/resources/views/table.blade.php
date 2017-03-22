@extends('pilot::layout')

@section('content')
    <div class="pilot-view-table">
        <div class="pilot-view-table--header clearfix">
            @can('create', $reflector['model'])
                <div class="float-sm-right">
                    <a href="{{ url(sprintf('%s/%s/edit', config('pilot.prefix'), $slug)) }}" class="btn btn-primary">
                        {!! $pilot->getIcon($reflector['icon']) !!} Add new
                    </a>
                </div>
            @endcan
            <h2>{{ $title }}</h2>
        </div>

        @if(!empty($scopes))
        <ul class="nav nav-tabs mb-1">
            <li class="nav-item">
                <a class="nav-link @if(empty($scope)) active @endif" href="{{ url(sprintf('%s/%s', config('pilot.prefix'), $slug)) }}">
                    All
                </a>
            </li>
            @foreach($scopes as $scopeSlug => $scopeName)
            <li class="nav-item">
                <a class="nav-link @if(!empty($scope) && $scope = $scopeSlug) active @endif" href="{{ url(sprintf('%s/%s/%s', config('pilot.prefix'), $slug, $scopeSlug)) }}">
                    {{ $scopeName }}
                </a>
            </li>
            @endforeach
        </ul>
        @endif

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-inverse">
                    <tr>
                        @foreach($reflector->getTableColumns() as $col)
                            <th>{{ $col['display'] }}</th>
                        @endforeach
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        @include('pilot::partials.table.row', ['item' => $item, 'columns' => $reflector->getTableColumns()])
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $items->links('pilot::partials.pagination') }}
    </div>
@endsection
