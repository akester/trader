<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the homepage with recent trades.
     */
    public function index()
    {
        $trades = Trade::where('status', '!=', 'create-failed')
            ->orderBy('created_at', 'desc')
            ->limit(50)->get();

        return view('home', compact('trades'));
    }
}
