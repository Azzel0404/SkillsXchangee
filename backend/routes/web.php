<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

Route::get('/debug', function () {
    return response()->json([
        'app_env' => app()->environment(),
        'app_debug' => config('app.debug'),
        'app_key_set' => !empty(config('app.key')),
        'db_connection' => config('database.default'),
        'db_host' => config('database.connections.mysql.host'),
        'db_port' => config('database.connections.mysql.port'),
        'db_database' => config('database.connections.mysql.database'),
        'timestamp' => now()->toISOString()
    ]);
});

Route::get('/health', function () {
    // Ultra-simple health check - just return 200 OK
    return response()->json(['status' => 'ok'], 200);
});

Route::get('/ping', function () {
    // Even simpler - just return text
    return 'pong';
});

Route::get('/test-laravel', function () {
    // Test if Laravel is working
    return response()->json([
        'status' => 'ok',
        'laravel' => 'working',
        'timestamp' => now()->toISOString(),
        'app_name' => config('app.name'),
        'app_env' => app()->environment()
    ]);
});

Route::get('/simple', function () {
    // Ultra-simple route that doesn't use any Laravel features
    return response('OK', 200);
});

Route::get('/health-detailed', function () {
    try {
        // Test database connection
        DB::connection()->getPdo();
        $dbStatus = 'connected';
    } catch (\Exception $e) {
        $dbStatus = 'failed: ' . $e->getMessage();
    }
    
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'database' => $dbStatus,
        'app_env' => app()->environment(),
        'app_debug' => config('app.debug'),
        'app_key_set' => !empty(config('app.key'))
    ]);
});

Route::get('/test-db', function () {
    try {
        // Test database connection
        $connection = DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();
        
        // Test if we can query users table
        $userCount = \App\Models\User::count();
        $testUser = \App\Models\User::where('email', 'test@example.com')->first();
        
        $result = [
            'status' => 'success',
            'message' => 'Database connection successful!',
            'database' => $dbName,
            'connection_type' => 'MySQL',
            'user_count' => $userCount,
            'test_user_exists' => $testUser ? 'YES' : 'NO',
            'test_user_details' => $testUser ? [
                'id' => $testUser->id,
                'name' => $testUser->name,
                'email' => $testUser->email,
                'created_at' => $testUser->created_at
            ] : null,
            'timestamp' => now()
        ];
        
        return response()->json($result, 200);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database connection failed!',
            'error' => $e->getMessage(),
            'timestamp' => now()
        ], 500);
    }
});

Route::get('/test-assets', function () {
    $buildPath = public_path('build');
    $manifestPath = $buildPath . '/manifest.json';
    
    $result = [
        'build_directory_exists' => is_dir($buildPath) ? 'YES' : 'NO',
        'manifest_exists' => file_exists($manifestPath) ? 'YES' : 'NO',
        'build_directory_contents' => is_dir($buildPath) ? array_diff(scandir($buildPath), ['.', '..']) : [],
        'manifest_content' => file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : null,
        'app_env' => app()->environment(),
        'app_debug' => config('app.debug'),
        'vite_assets' => app()->environment('production') ? 'Production mode - using built assets' : 'Development mode - using Vite dev server'
    ];
    
    return response()->json($result, 200);
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
        $user = auth()->user();
        
        if ($user->role === 'admin') {
            // Admin dashboard
            $pendingUsers = \App\Models\User::where('is_verified', false)->get();
            
            $stats = [
                'activeTrades' => \App\Models\Trade::where('status', 'active')->count(),
                'completedTrades' => \App\Models\Trade::where('status', 'completed')->count(),
                'pendingTrades' => \App\Models\Trade::where('status', 'pending')->count(),
                'pendingUsers' => $pendingUsers->count(),
                'totalTrades' => \App\Models\Trade::count(),
                'totalUsers' => \App\Models\User::count(),
                'verifiedUsers' => \App\Models\User::where('is_verified', true)->count(),
            ];
            
            return view('dashboard', compact('pendingUsers', 'stats'));
        } else {
            // User dashboard
            $userId = $user->id;
            
            // Get user's trades (both posted and participated in)
            $myTrades = \App\Models\Trade::where('user_id', $userId)->get();
            $participatedTrades = \App\Models\Trade::whereHas('requests', function($query) use ($userId) {
                $query->where('requester_id', $userId)->where('status', 'accepted');
            })->get();
            
            // Get all trades user is involved in
            $allUserTrades = $myTrades->merge($participatedTrades)->unique('id');
            
            // Categorize trades
            $completedSessions = $allUserTrades->where('status', 'completed');
            $ongoingSessions = $allUserTrades->where('status', 'active');
            
            // Get requests (exclude accepted ones from pending/declined lists)
            $myRequests = \App\Models\TradeRequest::where('requester_id', $userId)
                ->whereIn('status', ['pending', 'declined'])
                ->with(['trade.user', 'trade.offeringSkill', 'trade.lookingSkill'])
                ->get();
            
            $pendingRequests = $myRequests->where('status', 'pending');
            $declinedRequests = $myRequests->where('status', 'declined');
            
            // Get requests to user's trades (only pending ones)
            $requestsToMyTrades = \App\Models\TradeRequest::whereHas('trade', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })->where('status', 'pending')
            ->with(['requester', 'trade.offeringSkill', 'trade.lookingSkill'])
            ->get();
            
            $pendingRequestsToMe = $requestsToMyTrades;
            
            $userStats = [
                'completedSessions' => $completedSessions->count(),
                'ongoingSessions' => $ongoingSessions->count(),
                'pendingRequests' => $pendingRequests->count(),
                'declinedRequests' => $declinedRequests->count(),
                'pendingRequestsToMe' => $pendingRequestsToMe->count(),
            ];
            
            return view('dashboard', compact(
                'completedSessions', 
                'ongoingSessions', 
                'pendingRequests', 
                'declinedRequests', 
                'pendingRequestsToMe',
                'userStats'
            ));
        }
    })->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Trades (user dashboard area) - Restricted to regular users only
    Route::middleware('user.only')->group(function () {
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
        
        // Video call routes
        Route::post('/chat/{trade}/video-call/offer', [\App\Http\Controllers\VideoCallController::class, 'sendOffer'])->name('video-call.offer');
        Route::post('/chat/{trade}/video-call/answer', [\App\Http\Controllers\VideoCallController::class, 'sendAnswer'])->name('video-call.answer');
        Route::post('/chat/{trade}/video-call/ice-candidate', [\App\Http\Controllers\VideoCallController::class, 'sendIceCandidate'])->name('video-call.ice-candidate');
        Route::post('/chat/{trade}/video-call/end', [\App\Http\Controllers\VideoCallController::class, 'endCall'])->name('video-call.end');
    });
    
    // Admin functionality (moved from /admin to main dashboard) - Restricted to admin users only
    Route::middleware('admin')->group(function () {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/skills', [AdminController::class, 'skillsIndex'])->name('admin.skills.index');
        Route::get('/admin/skills/create', [AdminController::class, 'createSkill'])->name('admin.skill.create');
        Route::post('/admin/skills', [AdminController::class, 'storeSkill'])->name('admin.skill.store');
        Route::delete('/admin/skills/{skill}', [AdminController::class, 'deleteSkill'])->name('admin.skill.delete');
        Route::patch('/admin/approve/{user}', [AdminController::class, 'approve'])->name('admin.approve');
        Route::patch('/admin/reject/{user}', [AdminController::class, 'reject'])->name('admin.reject');
        Route::get('/admin/user/{user}', [AdminController::class, 'show'])->name('admin.user.show');
    });
});

// Admin routes available at /admin

require __DIR__.'/auth.php';
