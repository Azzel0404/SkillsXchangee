<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Events\VideoCallOffer;
use App\Events\VideoCallAnswer;
use App\Events\VideoCallIceCandidate;
use App\Events\VideoCallEnd;
use App\Models\Trade;

class VideoCallController extends Controller
{
    /**
     * Send a video call offer
     */
    public function sendOffer(Request $request, Trade $trade)
    {
        $user = Auth::user();
        
        // Verify user is part of this trade
        if (!$this->isUserInTrade($user, $trade)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'offer' => 'required|array',
            'callId' => 'required|string'
        ]);
        
        try {
            // Get the other user in the trade
            $otherUser = $this->getOtherUserInTrade($user, $trade);
            
            // Broadcast the offer
            event(new VideoCallOffer(
                $trade->id,
                $user->id,
                $user->firstname . ' ' . $user->lastname,
                $request->offer,
                $request->callId
            ));
            
            Log::info('Video call offer sent', [
                'trade_id' => $trade->id,
                'from_user_id' => $user->id,
                'to_user_id' => $otherUser->id,
                'call_id' => $request->callId
            ]);
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Error sending video call offer: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send offer'], 500);
        }
    }
    
    /**
     * Send a video call answer
     */
    public function sendAnswer(Request $request, Trade $trade)
    {
        $user = Auth::user();
        
        // Verify user is part of this trade
        if (!$this->isUserInTrade($user, $trade)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'answer' => 'required|array',
            'callId' => 'required|string',
            'toUserId' => 'required|integer'
        ]);
        
        try {
            // Broadcast the answer
            event(new VideoCallAnswer(
                $trade->id,
                $request->toUserId,
                $request->answer,
                $request->callId
            ));
            
            Log::info('Video call answer sent', [
                'trade_id' => $trade->id,
                'from_user_id' => $user->id,
                'to_user_id' => $request->toUserId,
                'call_id' => $request->callId
            ]);
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Error sending video call answer: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send answer'], 500);
        }
    }
    
    /**
     * Send ICE candidate
     */
    public function sendIceCandidate(Request $request, Trade $trade)
    {
        $user = Auth::user();
        
        // Verify user is part of this trade
        if (!$this->isUserInTrade($user, $trade)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'candidate' => 'required|array',
            'callId' => 'required|string',
            'toUserId' => 'required|integer'
        ]);
        
        try {
            // Broadcast the ICE candidate
            event(new VideoCallIceCandidate(
                $trade->id,
                $request->toUserId,
                $request->candidate,
                $request->callId
            ));
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Error sending ICE candidate: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send ICE candidate'], 500);
        }
    }
    
    /**
     * End video call
     */
    public function endCall(Request $request, Trade $trade)
    {
        $user = Auth::user();
        
        // Verify user is part of this trade
        if (!$this->isUserInTrade($user, $trade)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'callId' => 'required|string'
        ]);
        
        try {
            // Broadcast call end
            event(new VideoCallEnd(
                $trade->id,
                $user->id,
                $request->callId
            ));
            
            Log::info('Video call ended', [
                'trade_id' => $trade->id,
                'user_id' => $user->id,
                'call_id' => $request->callId
            ]);
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Error ending video call: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to end call'], 500);
        }
    }
    
    /**
     * Check if user is part of the trade
     */
    private function isUserInTrade($user, $trade)
    {
        return $user->id === $trade->user_id || $user->id === $trade->matched_user_id;
    }
    
    /**
     * Get the other user in the trade
     */
    private function getOtherUserInTrade($user, $trade)
    {
        if ($user->id === $trade->user_id) {
            return $trade->matchedUser;
        } else {
            return $trade->user;
        }
    }
}