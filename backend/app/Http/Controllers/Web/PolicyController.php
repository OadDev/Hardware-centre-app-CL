<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;

class PolicyController extends Controller
{
    public function privacy()
    {
        return view('web.policy-privacy', ['settings' => AppSetting::current()]);
    }

    public function terms()
    {
        return view('web.policy-terms', ['settings' => AppSetting::current()]);
    }

    public function returns()
    {
        return view('web.policy-returns', ['settings' => AppSetting::current()]);
    }

    public function shipping()
    {
        return view('web.policy-shipping', ['settings' => AppSetting::current()]);
    }
}
