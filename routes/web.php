<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/test', function () {
    return 'Test route working!';
});

Route::get('/test-auth', function () {
    if (auth()->check()) {
        return 'User is logged in: ' . auth()->user()->email;
    } else {
        return 'User is NOT logged in';
    }
});

Route::get('/debug-users', function () {
    $users = \App\Models\User::all();
    $output = '<h2>All Users in Database:</h2>';
    $output .= '<table border="1" style="border-collapse: collapse; width: 100%;">';
    $output .= '<tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Role</th><th>Verified</th><th>Created</th></tr>';
    
    foreach ($users as $user) {
        $output .= '<tr>';
        $output .= '<td>' . $user->id . '</td>';
        $output .= '<td>' . $user->firstname . ' ' . $user->lastname . '</td>';
        $output .= '<td>' . $user->email . '</td>';
        $output .= '<td>' . $user->username . '</td>';
        $output .= '<td>' . ($user->role ?? 'user') . '</td>';
        $output .= '<td>' . ($user->is_verified ? 'YES' : 'NO') . '</td>';
        $output .= '<td>' . $user->created_at . '</td>';
        $output .= '</tr>';
    }
    
    $output .= '</table>';
    
    $output .= '<h3>Summary:</h3>';
    $output .= '<p>Total Users: ' . $users->count() . '</p>';
    $output .= '<p>Verified Users: ' . $users->where('is_verified', true)->count() . '</p>';
    $output .= '<p>Pending Users: ' . $users->where('is_verified', false)->count() . '</p>';
    $output .= '<p>Admin Users: ' . $users->where('role', 'admin')->count() . '</p>';
    
    return $output;
});

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        Log::info('Dashboard route hit!');
        $pendingUsers = \App\Models\User::where('is_verified', false)->get();
        
        // Get real stats from the database
        $stats = [
            'activeTrades' => \App\Models\Trade::where('status', 'active')->count(),
            'completedTrades' => \App\Models\Trade::where('status', 'completed')->count(),
            'pendingTrades' => \App\Models\Trade::where('status', 'pending')->count(),
            'pendingUsers' => $pendingUsers->count(),
            'totalTrades' => \App\Models\Trade::count(),
            'myTrades' => \App\Models\Trade::where('user_id', auth()->id())->count(),
            'myRequests' => \App\Models\TradeRequest::where('requester_id', auth()->id())->count(),
        ];
        
        Log::info('Found ' . $pendingUsers->count() . ' pending users');
        return view('dashboard', compact('pendingUsers', 'stats'));
    })->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Trades (user dashboard area)
    Route::get('/trades/create', [\App\Http\Controllers\TradeController::class, 'create'])->name('trades.create');
    Route::post('/trades', [\App\Http\Controllers\TradeController::class, 'store'])->name('trades.store');
    Route::get('/trades/matches', [\App\Http\Controllers\TradeController::class, 'matches'])->name('trades.matches');
    Route::get('/trades/requests', [\App\Http\Controllers\TradeController::class, 'requests'])->name('trades.requests');
    Route::get('/trades/ongoing', [\App\Http\Controllers\TradeController::class, 'ongoing'])->name('trades.ongoing');
    Route::get('/trades/notifications', [\App\Http\Controllers\TradeController::class, 'notify'])->name('trades.notifications');
    
    // Trade request actions
    Route::post('/trades/{trade}/request', [\App\Http\Controllers\TradeController::class, 'requestTrade'])->name('trades.request');
    Route::post('/trade-requests/{tradeRequest}/respond', [\App\Http\Controllers\TradeController::class, 'respondToRequest'])->name('trades.respond');
    
    // Notification actions
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\TradeController::class, 'markNotificationAsRead'])->name('trades.mark-read');
    
    // Chat routes
    Route::get('/chat/{trade}', [\App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
    Route::get('/chat/{trade}/messages', [\App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/{trade}/message', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send-message');
    Route::post('/chat/{trade}/task', [\App\Http\Controllers\ChatController::class, 'createTask'])->name('chat.create-task');
    Route::patch('/chat/task/{task}/toggle', [\App\Http\Controllers\ChatController::class, 'toggleTask'])->name('chat.toggle-task');
    
    // Admin functionality (moved from /admin to main dashboard)
    Route::get('/admin/skills', [AdminController::class, 'skillsIndex'])->name('admin.skills.index');
    Route::get('/admin/skills/create', [AdminController::class, 'createSkill'])->name('admin.skill.create');
    Route::post('/admin/skills', [AdminController::class, 'storeSkill'])->name('admin.skill.store');
    Route::delete('/admin/skills/{skill}', [AdminController::class, 'deleteSkill'])->name('admin.skill.delete');
    Route::patch('/admin/approve/{user}', [AdminController::class, 'approve'])->name('admin.approve');
    Route::patch('/admin/reject/{user}', [AdminController::class, 'reject'])->name('admin.reject');
    Route::get('/admin/user/{user}', [AdminController::class, 'show'])->name('admin.user.show');
});

// Admin routes moved to main dashboard - no separate /admin route

require __DIR__.'/auth.php';
