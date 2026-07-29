<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class DownloadAppController extends Controller
{
    public function index()
    {
        return view('web.download-app');
    }
}
