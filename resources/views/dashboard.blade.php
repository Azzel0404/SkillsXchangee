@extends('layouts.app')

@section('content')
<div class="py-4 py-md-5">
    <div class="container">
        <!-- Welcome Section -->
        <div class="mb-4 mb-md-5">
            <h1 class="h2 fw-bold text-dark mb-2">Welcome back, {{ auth()->user()->firstname }}!</h1>
            <p class="text-muted">Here's what's happening with your skill trades today.</p>
        </div>

        @if(auth()->user()->role === 'admin')
        <!-- Admin Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary rounded-3 d-flex align-items-center justify-content-center"
                                    style="width: 48px; height: 48px;">
                                    <i class="fas fa-users text-white"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <p class="text-muted small mb-1">Total Users</p>
                                <h3 class="fw-bold mb-0">{{ $stats['totalUsers'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success rounded-3 d-flex align-items-center justify-content-center"
                                    style="width: 48px; height: 48px;">
                                    <i class="fas fa-check-circle text-white"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <p class="text-muted small mb-1">Verified Users</p>
                                <h3 class="fw-bold mb-0">{{ $stats['verifiedUsers'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning rounded-3 d-flex align-items-center justify-content-center"
                                    style="width: 48px; height: 48px;">
                                    <i class="fas fa-clock text-white"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <p class="text-muted small mb-1">Pending Users</p>
                                <h3 class="fw-bold mb-0">{{ $stats['pendingUsers'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-info rounded-3 d-flex align-items-center justify-content-center"
                                    style="width: 48px; height: 48px;">
                                    <i class="fas fa-exchange-alt text-white"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <p class="text-muted small mb-1">Total Trades</p>
                                <h3 class="fw-bold mb-0">{{ $stats['totalTrades'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- User Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success rounded-3 d-flex align-items-center justify-content-center"
                                    style="width: 48px; height: 48px;">
                                    <i class="fas fa-check-circle text-white"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <p class="text-muted small mb-1">Completed Sessions</p>
                                <h3 class="fw-bold mb-0">{{ $userStats['completedSessions'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary rounded-3 d-flex align-items-center justify-content-center"
                                    style="width: 48px; height: 48px;">
                                    <i class="fas fa-clock text-white"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <p class="text-muted small mb-1">Ongoing Sessions</p>
                                <h3 class="fw-bold mb-0">{{ $userStats['ongoingSessions'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning rounded-3 d-flex align-items-center justify-content-center"
                                    style="width: 48px; height: 48px;">
                                    <i class="fas fa-hourglass-half text-white"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <p class="text-muted small mb-1">Pending Requests</p>
                                <h3 class="fw-bold mb-0">{{ $userStats['pendingRequests'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-danger rounded-3 d-flex align-items-center justify-content-center"
                                    style="width: 48px; height: 48px;">
                                    <i class="fas fa-times-circle text-white"></i>
                                </div>
                            </div>
                            <div class="ms-3">
                                <p class="text-muted small mb-1">Declined Requests</p>
                                <h3 class="fw-bold mb-0">{{ $userStats['declinedRequests'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Expired Sessions -->
        @if($expiredSessions->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Expired Sessions ({{
                    $expiredSessions->count() }})</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-clock me-2"></i>
                    <strong>Session Expired!</strong> The following sessions have passed their scheduled time and are
                    now marked as expired.
                </div>
                <div class="row g-3">
                    @foreach($expiredSessions->take(3) as $session)
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-warning">
                            <div class="card-body">
                                <h6 class="card-title text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    {{ $session->offeringSkill->name }} ↔ {{ $session->lookingSkill->name }}
                                </h6>
                                <p class="card-text small text-muted">
                                    <strong>Ended:</strong> {{ $session->end_date ?
                                    \Carbon\Carbon::parse($session->end_date)->format('M d, Y') : 'N/A' }}
                                </p>
                                <p class="card-text small text-muted">
                                    <strong>Status:</strong> <span class="badge bg-warning">Expired</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($expiredSessions->count() > 3)
                <div class="text-center mt-3">
                    <small class="text-muted">And {{ $expiredSessions->count() - 3 }} more expired sessions...</small>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 fw-bold text-dark mb-4">Quick Actions</h2>
                <div class="row g-3">
                    @if(auth()->user()->role !== 'admin')
                    <div class="col-md-4">
                        <a href="{{ route('trades.create') }}" class="text-decoration-none">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <div class="bg-primary rounded-3 d-inline-flex align-items-center justify-content-center mb-3"
                                        style="width: 48px; height: 48px;">
                                        <i class="fas fa-plus text-white"></i>
                                    </div>
                                    <h5 class="card-title text-dark">Post a Trade</h5>
                                    <p class="card-text text-muted small">Create a new skill trade post</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <a href="{{ route('trades.matches') }}" class="text-decoration-none">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <div class="bg-success rounded-3 d-inline-flex align-items-center justify-content-center mb-3"
                                        style="width: 48px; height: 48px;">
                                        <i class="fas fa-search text-white"></i>
                                    </div>
                                    <h5 class="card-title text-dark">Find Matches</h5>
                                    <p class="card-text text-muted small">Browse available trades</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif

                    <div class="col-md-4">
                        <a href="{{ route('profile.edit') }}" class="text-decoration-none">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <div class="bg-info rounded-3 d-inline-flex align-items-center justify-content-center mb-3"
                                        style="width: 48px; height: 48px;">
                                        <i class="fas fa-user-edit text-white"></i>
                                    </div>
                                    <h5 class="card-title text-dark">Update Profile</h5>
                                    <p class="card-text text-muted small">Manage your skills and info</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->role !== 'admin')
        <!-- User Sessions and Requests -->

        <!-- Completed Sessions -->
        @if($completedSessions->count() > 0)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Completed Sessions</h2>
                <div class="space-y-4">
                    @foreach($completedSessions->take(5) as $session)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-900">
                                    {{ $session->offeringSkill->name }} ↔ {{ $session->lookingSkill->name }}
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    @if($session->user_id === auth()->id())
                                    You offered {{ $session->offeringSkill->name }} and learned {{
                                    $session->lookingSkill->name }}
                                    @else
                                    You learned {{ $session->offeringSkill->name }} and offered {{
                                    $session->lookingSkill->name }}
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400 mt-2">
                                    Completed on {{ $session->updated_at->format('M d, Y') }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Completed
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Ongoing Sessions -->
        @if($ongoingSessions->count() > 0)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ongoing Sessions</h2>
                <div class="space-y-4">
                    @foreach($ongoingSessions->take(5) as $session)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-900">
                                    {{ $session->offeringSkill->name }} ↔ {{ $session->lookingSkill->name }}
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    @if($session->user_id === auth()->id())
                                    You're offering {{ $session->offeringSkill->name }} and learning {{
                                    $session->lookingSkill->name }}
                                    @else
                                    You're learning {{ $session->offeringSkill->name }} and offering {{
                                    $session->lookingSkill->name }}
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400 mt-2">
                                    Started on {{ $session->start_date->format('M d, Y') }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Active
                                </span>
                                <a href="{{ route('chat.session', $session->id) }}"
                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                    View Chat
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Pending Requests -->
        @if($pendingRequests->count() > 0)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Your Pending Requests</h2>
                <div class="space-y-4">
                    @foreach($pendingRequests->take(5) as $request)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-900">
                                    Request to {{ $request->trade->user->firstname }} {{ $request->trade->user->lastname
                                    }}
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    You want to learn {{ $request->trade->offeringSkill->name }} and offer {{
                                    $request->trade->lookingSkill->name }}
                                </p>
                                @if($request->message)
                                <p class="text-sm text-gray-600 mt-2 italic">"{{ $request->message }}"</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-2">
                                    Sent on {{ $request->created_at->format('M d, Y') }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Declined Requests -->
        @if($declinedRequests->count() > 0)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Declined Requests</h2>
                <div class="space-y-4">
                    @foreach($declinedRequests->take(5) as $request)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-900">
                                    Request to {{ $request->trade->user->firstname }} {{ $request->trade->user->lastname
                                    }}
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    You wanted to learn {{ $request->trade->offeringSkill->name }} and offer {{
                                    $request->trade->lookingSkill->name }}
                                </p>
                                <p class="text-xs text-gray-400 mt-2">
                                    Declined on {{ $request->responded_at->format('M d, Y') }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Declined
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Requests to Your Trades -->
        @if($pendingRequestsToMe->count() > 0)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Requests to Your Trades</h2>
                <div class="space-y-4">
                    @foreach($pendingRequestsToMe->take(5) as $request)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-900">
                                    Request from {{ $request->requester->firstname }} {{ $request->requester->lastname
                                    }}
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Wants to learn {{ $request->trade->offeringSkill->name }} and offer {{
                                    $request->trade->lookingSkill->name }}
                                </p>
                                @if($request->message)
                                <p class="text-sm text-gray-600 mt-2 italic">"{{ $request->message }}"</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-2">
                                    Received on {{ $request->created_at->format('M d, Y') }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                                <a href="{{ route('trades.show', $request->trade->id) }}"
                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                    Review
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        @endif

        <!-- Admin Section (if admin) -->
        @if(auth()->user()->role === 'admin')
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Admin Panel</h2>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-md font-medium text-gray-700">Pending Users ({{ $pendingUsers->count() }})</h3>
                    <a href="{{ route('admin.skills.index') }}"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm">
                        Manage Skills
                    </a>
                </div>
                @if($pendingUsers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Name</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Email</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Skill</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pendingUsers->take(3) as $user)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    {{ $user->firstname }} {{ $user->lastname }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $user->email }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ optional($user->skill)->name ?? '—' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap space-x-2">
                                    <a href="{{ route('admin.user.show', $user->id) }}"
                                        class="px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600 transition">
                                        View
                                    </a>
                                    <form action="{{ route('admin.approve', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="px-2 py-1 bg-green-500 text-white rounded text-xs hover:bg-green-600 transition">
                                            Approve
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($pendingUsers->count() > 3)
                    <div class="mt-4 text-center">
                        <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">View all {{ $pendingUsers->count()
                            }} pending users</a>
                    </div>
                    @endif
                </div>
                @else
                <p class="text-gray-500 text-sm">No pending users at the moment.</p>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection