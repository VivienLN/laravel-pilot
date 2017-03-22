<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>

        <title>@section('title') Pilot @show</title>

        <!-- Styles -->
        <link href="{{ asset('vendor/pilot/css/pilot.min.css') }}" rel="stylesheet" />

        <script>
            var APP = {
                csrf_token: "{{ csrf_token() }}"
            }
        </script>
    </head>
    <body class="page-login">
        <div id="wrapper">
            <form class="form" role="form" method="POST" action="">
                {{ csrf_field() }}

                <div class="form-group{{ $errors->has('email') ? ' has-danger' : '' }}">
                    <label for="email" class="control-label">E-Mail Address</label>
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="user@domain.com">

                    @if ($errors->has('email'))
                        <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                    @endif
                </div>

                <div class="form-group{{ $errors->has('password') ? ' has-danger' : '' }}">
                    <label for="password" class="control-label">Password</label>
                    <input id="password" type="password" class="form-control" name="password" placeholder="password" required>

                    @if ($errors->has('password'))
                        <span class="help-block">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                    @endif
                </div>

                <div class="form-group">
                    <label class="custom-control custom-checkbox">
                        <input type="checkbox" name="remember" class="custom-control-input">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">Remember Me</span>
                    </label>
                </div>


                <button type="submit" class="btn btn-primary btn-block btn-lg">{!! $pilot->getIcon('lock') !!}Login</button>
            </form>
        </div>
    </body>
</html>
