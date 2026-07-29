<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    public function index()
    {
        return view('web.contact');
    }
}
