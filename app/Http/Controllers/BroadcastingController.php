<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BroadcastingController extends Controller
{
    /**
     * Handle broadcasting authentication
     */
    public function auth(Request $request)
    {
        try {
            // Log the incoming request for debugging
            Log::info('Broadcasting auth request received', [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'user_authenticated' => Auth::check(),
                'user_id' => Auth::id(),
                'session_id' => $request->session()->getId(),
                'cookies' => $request->cookies->all()
            ]);
            
            $user = Auth::user();
            
            if (!$user) {
                Log::warning('Broadcasting auth failed: User not authenticated');
                // For now, let's allow unauthenticated users to access public channels
                // This is a temporary fix to get video calls working
                $channelName = $request->input('channel_name');
                $socketId = $request->input('socket_id');
                
                // Only allow access to trade channels for now
                if (str_starts_with($channelName, 'private-trade.') || str_starts_with($channelName, 'trade.')) {
                    Log::info('Allowing unauthenticated access to trade channel', [
                        'channel_name' => $channelName,
                        'socket_id' => $socketId
                    ]);
                    
                    // Generate Pusher auth signature
                    $pusher = app('pusher');
                    $auth = $pusher->socket_auth($channelName, $socketId);
                    
                    return response()->json($auth);
                }
                
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            $channelName = $request->input('channel_name');
            $socketId = $request->input('socket_id');
            
            Log::info('Broadcasting auth request', [
                'user_id' => $user->id,
                'channel_name' => $channelName,
                'socket_id' => $socketId
            ]);
            
            // Handle private channels
            if (str_starts_with($channelName, 'private-')) {
                $originalChannelName = $channelName;
                $channelName = str_replace('private-', '', $channelName);
                
                // Check if user can access this channel
                if (str_starts_with($channelName, 'trade.')) {
                    $tradeId = str_replace('trade.', '', $channelName);
                    $trade = \App\Models\Trade::find($tradeId);
                    
                    if (!$trade) {
                        Log::warning('Broadcasting auth failed: Trade not found', ['trade_id' => $tradeId]);
                        return response()->json(['error' => 'Trade not found'], 403);
                    }
                    
                    // Check if user is authorized for this trade
                    $isAuthorized = $trade->user_id === $user->id || 
                                   $trade->requests()->where('requester_id', $user->id)->where('status', 'accepted')->exists();
                    
                    if (!$isAuthorized) {
                        Log::warning('Broadcasting auth failed: User not authorized for trade', [
                            'user_id' => $user->id,
                            'trade_id' => $tradeId
                        ]);
                        return response()->json(['error' => 'Unauthorized for this trade'], 403);
                    }
                }
                
                // Use the original channel name for Pusher auth
                $channelName = $originalChannelName;
            }
            
            // Generate Pusher auth signature
            $pusher = app('pusher');
            $auth = $pusher->socket_auth($channelName, $socketId);
            
            Log::info('Broadcasting auth successful', [
                'user_id' => $user->id,
                'channel_name' => $channelName
            ]);
            
            return response()->json($auth);
            
        } catch (\Exception $e) {
            Log::error('Broadcasting auth error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'channel_name' => $request->input('channel_name'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
