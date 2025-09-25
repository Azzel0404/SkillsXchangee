<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Skill;
use App\Models\Trade;
use App\Models\TradeRequest;
use App\Models\TradeTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            return $this->adminDashboard();
        }
        
        return $this->userDashboard($user);
    }
    
    private function adminDashboard()
    {
        // Get pending users for admin functionality
        $pendingUsers = User::where('is_verified', false)->get();
        
        // Admin statistics
        $stats = [
            'totalUsers' => User::count(),
            'verifiedUsers' => User::where('is_verified', true)->count(),
            'pendingUsers' => $pendingUsers->count(),
            'totalTrades' => Trade::count(),
        ];
        
        // Get expired sessions
        $expiredSessions = Trade::where('status', 'expired')
            ->with(['offeringSkill', 'lookingSkill'])
            ->get();
        
        Log::info('AdminDashboard: Loading with ' . $pendingUsers->count() . ' pending users');
        
        return view('dashboard', compact('stats', 'pendingUsers', 'expiredSessions'));
    }
    
    private function userDashboard($user)
    {
        // Calculate user-specific statistics
        $userStats = $this->getUserStatistics($user);
        
        // Get expired sessions for this user
        $expiredSessions = Trade::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('requests', function($q) use ($user) {
                          $q->where('requester_id', $user->id)
                            ->where('status', 'accepted');
                      });
            })
            ->where('status', 'expired')
            ->with(['offeringSkill', 'lookingSkill'])
            ->get();
        
        Log::info('UserDashboard: Loading for user ' . $user->id . ' with stats: ' . json_encode($userStats));
        
        return view('dashboard', compact('userStats', 'expiredSessions'));
    }
    
    private function getUserStatistics($user)
    {
        // Debug: Log user ID for troubleshooting
        Log::info('Calculating statistics for user: ' . $user->id);
        
        // Completed Sessions: Trades where user participated and status is 'closed'
        $completedSessions = Trade::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('requests', function($q) use ($user) {
                          $q->where('requester_id', $user->id)
                            ->where('status', 'accepted');
                      });
            })
            ->where('status', 'closed')
            ->count();
        
        // Ongoing Sessions: Trades where user participated and status is 'ongoing' or 'open'
        // (Open trades with accepted requests should be considered ongoing)
        $ongoingSessions = Trade::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('requests', function($q) use ($user) {
                          $q->where('requester_id', $user->id)
                            ->where('status', 'accepted');
                      });
            })
            ->whereIn('status', ['ongoing', 'open'])
            ->count();
        
        // Log the calculated ongoing sessions count
        Log::info('Ongoing sessions calculated: ' . $ongoingSessions);
        
        // Pending Requests: Trade requests sent by this user that are still pending
        $pendingRequests = TradeRequest::where('requester_id', $user->id)
            ->where('status', 'pending')
            ->count();
        
        // Declined Requests: Trade requests sent by this user that were declined
        $declinedRequests = TradeRequest::where('requester_id', $user->id)
            ->where('status', 'declined')
            ->count();
        
        // Additional statistics for better insights
        $totalTradesCreated = Trade::where('user_id', $user->id)->count();
        $totalRequestsReceived = TradeRequest::whereHas('trade', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();
        
        $stats = [
            'completedSessions' => $completedSessions,
            'ongoingSessions' => $ongoingSessions,
            'pendingRequests' => $pendingRequests,
            'declinedRequests' => $declinedRequests,
            'totalTradesCreated' => $totalTradesCreated,
            'totalRequestsReceived' => $totalRequestsReceived,
        ];
        
        Log::info('Dashboard statistics calculated:', $stats);
        
        return $stats;
    }
}
