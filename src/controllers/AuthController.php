<?php

namespace VivienLN\Pilot\Controllers;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use \Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use VivienLN\Pilot\Pilot;
use VivienLN\Pilot\PilotComposer;
use VivienLN\Pilot\PilotRole;

class AuthController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Show login form
     * @param Request $request
     */
    public function showLoginForm(Request $request, Pilot $pilot)
    {
        return view($pilot->getViewName('login'), [
            'title' => 'Login',
        ]);
    }

    /**
     * Attempt to log in. This is very similar to Auth\AuthenticatesUsers
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        // check fields
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        // redirect
        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        // original check with defined guard
        $result = $this->guard()->attempt(
            $this->credentials($request), $request->has('remember')
        );
        // failed
        if(!$result) {
            return false;
        }
        // now check if user is admin
        return PilotRole::contains($this->guard()->user());
    }

    /***
     * By default, return to admin panel homepage
     */
    public function redirectPath()
    {
        return config('pilot.prefix');
    }
}