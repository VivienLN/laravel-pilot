<?php
/**
 * Created by PhpStorm.
 * User: Vivien
 * Date: 21/09/2016
 * Time: 17:34
 */

namespace VivienLN\Pilot;

use Illuminate\Http\Request;
use Illuminate\View\View;


class PilotComposer
{
    private $_request;
    private $_pilot;

    public function __construct(Request $request, Pilot $pilot)
    {
        $this->_request = $request;
        $this->_pilot = $pilot;
    }

    public function compose(View $view)
    {
        $view->with('pilot', $this->_pilot);
        $view->with('user', $this->_request->user());
    }
}