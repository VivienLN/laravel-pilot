<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@section('title') {{ $pilot->getConfig()['title'] }} @show</title>
    
    <!-- Styles -->
    <link href="{{ asset('vendor/pilot/css/pilot.min.css') }}" rel="stylesheet" />
    
    <script>
        var APP = {
            csrf_token: "{{ csrf_token() }}",
            upload_url: "{{ url(config('pilot.prefix') . '/upload') }}",
            asset_path: "{{ asset('/') }}"
        }
    </script>

</head>
<body>
    <div class="wrapper">
        @section('header')
            <header class="header-main clearfix">
                <span class="float-sm-right"><strong>{{ $user->email }}</strong> &mdash; <a href="{{ url(sprintf('%s/logout', config('pilot.prefix'))) }}" class="">Logout</a></span>
                <h1>Administration</h1>
            </header>
        @show
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-5 col-md-4 col-lg-3 col-xl-2 sidebar-main">
                    <ul class="nav">
                        <?php
                            $active = Request::is(config('pilot.prefix'));
                        ?>
                        <li><a href="{{ url(config('pilot.prefix')) }}" class="@if($active) active @endif">{!! $pilot->getIcon('dashboard') !!} <span>Dashboard</span></a></li>
                    </ul>
                    <ul class="nav">
                        @foreach($pilot->getModels() as $menuItem)
                            <?php
                                $r = $menuItem['reflector'] ?? null;
                                $uri = sprintf('%s/%s', config('pilot.prefix'), $menuItem['slug']);
                                $active = isset($slug) && $menuItem['slug'] == $slug;
                            ?>
                            @if(!$r || ($r && Auth::user()->can('list', $r['model'])))
                                <li>
                                    <a class="@if($active) active @endif" href="{{ url($uri) }}">
                                        {!! $pilot->getIcon($menuItem['icon']) !!}
                                        <span>{{ $menuItem['display'] }}</span>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                <div class="col-sm-7 col-sm-8 col-lg-9 col-xl-10 content-main">
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible">
                            {{ session('status') }}
                        </div>
                    @endif
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="{{ asset('vendor/pilot/js/pilot.min.js') }}" type="text/javascript"></script>
    <!--   <script src="{{ asset('js/admin.min.js') }}"></script> -->
    <script>
        $(document).ready(function() {
            $(window).resize(function() {
                $('.sidebar-main').css('min-height', $(document).height());
            }).resize();
        });
    </script>
    @yield('footer-scripts')
</body>
</html>
