<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Models\TradeMessage;
use App\Models\TradeTask;
use App\Events\MessageSent;
use App\Events\TaskUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function show(Trade $trade)
    {
        $user = Auth::user();
        
        // Check if user is part of this trade
        if ($trade->user_id !== $user->id && 
            !$trade->requests()->where('requester_id', $user->id)->where('status', 'accepted')->exists()) {
            return redirect()->back()->with('error', 'You are not authorized to view this trade chat.');
        }

        // Get the other user (trade partner)
        $partner = $trade->user_id === $user->id 
            ? $trade->requests()->where('status', 'accepted')->first()->requester
            : $trade->user;

        // Get messages
        $messages = $trade->messages()->with('sender')->orderBy('created_at', 'asc')->get();

        // Get tasks
        $myTasks = $trade->tasks()->where('assigned_to', $user->id)->get();
        $partnerTasks = $trade->tasks()->where('assigned_to', $partner->id)->get();

        // Calculate progress
        $myProgress = $myTasks->count() > 0 ? ($myTasks->where('completed', true)->count() / $myTasks->count()) * 100 : 0;
        $partnerProgress = $partnerTasks->count() > 0 ? ($partnerTasks->where('completed', true)->count() / $partnerTasks->count()) * 100 : 0;

        return view('chat.session', compact('trade', 'partner', 'messages', 'myTasks', 'partnerTasks', 'myProgress', 'partnerProgress'));
    }

    public function sendMessage(Request $request, Trade $trade)
    {
        try {
            $user = Auth::user();
            
            // Check if user is part of this trade
            if ($trade->user_id !== $user->id && 
                !$trade->requests()->where('requester_id', $user->id)->where('status', 'accepted')->exists()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $request->validate([
                'message' => 'required|string|max:1000'
            ]);

            $message = $trade->messages()->create([
                'sender_id' => $user->id,
                'message' => $request->message
            ]);

            $message->load('sender');

            // Broadcast message using Laravel events
            try {
                event(new MessageSent($message, $trade->id));
            } catch (\Exception $e) {
                Log::error('Broadcasting failed: ' . $e->getMessage());
                // Continue even if broadcasting fails
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Message send error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createTask(Request $request, Trade $trade)
    {
        try {
            $user = Auth::user();
            
            // Check if user is part of this trade
            if ($trade->user_id !== $user->id && 
                !$trade->requests()->where('requester_id', $user->id)->where('status', 'accepted')->exists()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'assigned_to' => 'required|exists:users,id'
            ]);

            $task = $trade->tasks()->create([
                'created_by' => $user->id,
                'assigned_to' => $request->assigned_to,
                'title' => $request->title,
                'description' => $request->description
            ]);

            $task->load(['creator', 'assignee']);

            // Broadcast task update using Laravel events
            try {
                event(new TaskUpdated($task, $trade->id));
            } catch (\Exception $e) {
                Log::error('Broadcasting failed: ' . $e->getMessage());
                // Continue even if broadcasting fails
            }

            return response()->json([
                'success' => true,
                'task' => $task
            ]);
        } catch (\Exception $e) {
            Log::error('Task creation error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to create task: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getMessages(Trade $trade)
    {
        try {
            $user = Auth::user();
            
            // Check if user is part of this trade
            if ($trade->user_id !== $user->id && 
                !$trade->requests()->where('requester_id', $user->id)->where('status', 'accepted')->exists()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $messages = $trade->messages()->with('sender')->orderBy('created_at', 'asc')->get();
            
            return response()->json([
                'success' => true,
                'count' => $messages->count(),
                'messages' => $messages
            ]);
        } catch (\Exception $e) {
            Log::error('Get messages error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to get messages: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleTask(Request $request, TradeTask $task)
    {
        try {
            $user = Auth::user();
            
            // Check if user is assigned to this task
            if ($task->assigned_to !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $task->update([
                'completed' => !$task->completed,
                'completed_at' => !$task->completed ? now() : null
            ]);

            $task->load(['creator', 'assignee']);

            // Broadcast task update using Laravel events
            try {
                event(new TaskUpdated($task, $task->trade_id));
            } catch (\Exception $e) {
                Log::error('Broadcasting failed: ' . $e->getMessage());
                // Continue even if broadcasting fails
            }

            return response()->json([
                'success' => true,
                'task' => $task
            ]);
        } catch (\Exception $e) {
            Log::error('Task toggle error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update task: ' . $e->getMessage()
            ], 500);
        }
    }
}