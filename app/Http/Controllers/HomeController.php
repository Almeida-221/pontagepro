<?php

namespace App\Http\Controllers;

use App\Models\Module;

class HomeController extends Controller
{
    public function index()
    {
        $modules = Module::active()->with(['plans' => function ($q) {
            $q->where('is_active', true)->orderBy('price');
        }])->get();

        return view('welcome', compact('modules'));
    }
}
