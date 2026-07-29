<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class SupportController extends Controller
{
    public function index()
    {
        return view('web.support');
    }
}
