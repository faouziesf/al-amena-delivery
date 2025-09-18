<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;

class DelivererWithdrawalController extends Controller
{
    public function index()
    {
        return response()->json(['temp' => 'controller']);
    }
}