<?php

namespace App\Http\Controllers\Commercial;


use App\Http\Controllers\Controller;

class CommercialTopupController extends Controller
{
    public function index()
    {
        return response()->json(['temp' => 'controller']);
    }
}