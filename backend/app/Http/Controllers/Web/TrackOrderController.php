<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class TrackOrderController extends Controller
{
    public function index()
    {
        return view('web.track-order');
    }
}
