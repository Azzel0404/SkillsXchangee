<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        // Get pending users for admin functionality
        $pendingUsers = User::where('is_verified', false)->get();
        
        // Debug: Log that we're in the dashboard controller
        Log::info('DashboardController: Loading dashboard with ' . $pendingUsers->count() . ' pending users');
        
        return view('dashboard', compact('pendingUsers'));
    }
}
