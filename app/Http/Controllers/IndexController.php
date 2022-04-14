<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Collection;
use Notion;

class IndexController extends Controller
{
    //
    public function index()
    {
        return view('welcome');
    }
}
