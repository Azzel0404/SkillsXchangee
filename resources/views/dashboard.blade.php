@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome back, {{ auth()->user()->firstname }}!</h1>
                <p class="text-gray-600">Here's what's happening with your skill trades today.</p>
            </div>

            @if(auth()->user()->role === 'admin')
                <!-- Admin Stats Cards -->
                <div class="dashboard-grid">
                    <div class="card">
                        <div class="card-body">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Users</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['totalUsers'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Verified Users</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['verifiedUsers'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Pending Users</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['pendingUsers'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-6H4v6z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Trades</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['totalTrades'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- User Stats Cards -->
                <div class="dashboard-grid">
                    <div class="card">
                        <div class="card-body">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Completed Sessions</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $userStats['completedSessions'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Ongoing Sessions</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $userStats['ongoingSessions'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Pending Requests</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $userStats['pendingRequests'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Declined Requests</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $userStats['declinedRequests'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @if(auth()->user()->role !== 'admin')
                        <a href="{{ route('trades.create') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-900">Post a Trade</h3>
                                <p class="text-sm text-gray-500">Create a new skill trade post</p>
                            </div>
                        </a>

                        <a href="{{ route('trades.matches') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-900">Find Matches</h3>
                                <p class="text-sm text-gray-500">Browse available trades</p>
                            </div>
                        </a>
                        @endif

                        <a href="{{ route('profile.edit') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-900">Update Profile</h3>
                                <p class="text-sm text-gray-500">Manage your skills and info</p>
                            </div>
                        </a>
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
                                                You offered {{ $session->offeringSkill->name }} and learned {{ $session->lookingSkill->name }}
                                            @else
                                                You learned {{ $session->offeringSkill->name }} and offered {{ $session->lookingSkill->name }}
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-400 mt-2">
                                            Completed on {{ $session->updated_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
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
                                                You're offering {{ $session->offeringSkill->name }} and learning {{ $session->lookingSkill->name }}
                                            @else
                                                You're learning {{ $session->offeringSkill->name }} and offering {{ $session->lookingSkill->name }}
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-400 mt-2">
                                            Started on {{ $session->start_date->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Active
                                        </span>
                                        <a href="{{ route('chat.session', $session->id) }}" class="text-blue-600 hover:text-blue-800 text-sm">
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
                                            Request to {{ $request->trade->user->firstname }} {{ $request->trade->user->lastname }}
                                        </h3>
                                        <p class="text-sm text-gray-500 mt-1">
                                            You want to learn {{ $request->trade->offeringSkill->name }} and offer {{ $request->trade->lookingSkill->name }}
                                        </p>
                                        @if($request->message)
                                        <p class="text-sm text-gray-600 mt-2 italic">"{{ $request->message }}"</p>
                                        @endif
                                        <p class="text-xs text-gray-400 mt-2">
                                            Sent on {{ $request->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
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
                                            Request to {{ $request->trade->user->firstname }} {{ $request->trade->user->lastname }}
                                        </h3>
                                        <p class="text-sm text-gray-500 mt-1">
                                            You wanted to learn {{ $request->trade->offeringSkill->name }} and offer {{ $request->trade->lookingSkill->name }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-2">
                                            Declined on {{ $request->responded_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
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
                                            Request from {{ $request->requester->firstname }} {{ $request->requester->lastname }}
                                        </h3>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Wants to learn {{ $request->trade->offeringSkill->name }} and offer {{ $request->trade->lookingSkill->name }}
                                        </p>
                                        @if($request->message)
                                        <p class="text-sm text-gray-600 mt-2 italic">"{{ $request->message }}"</p>
                                        @endif
                                        <p class="text-xs text-gray-400 mt-2">
                                            Received on {{ $request->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                        <a href="{{ route('trades.show', $request->trade->id) }}" class="text-blue-600 hover:text-blue-800 text-sm">
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
                        <a href="{{ route('admin.skills.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm">
                            Manage Skills
                        </a>
                    </div>
                    @if($pendingUsers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Email</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Skill</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Action</th>
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
                                            <a href="{{ route('admin.user.show', $user->id) }}" class="px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600 transition">
                                                View
                                            </a>
                                            <form action="{{ route('admin.approve', $user->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-2 py-1 bg-green-500 text-white rounded text-xs hover:bg-green-600 transition">
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
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">View all {{ $pendingUsers->count() }} pending users</a>
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
