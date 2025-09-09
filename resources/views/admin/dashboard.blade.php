@extends('layouts.app')

@section('content')
<main style="padding:32px;">
    <h1 style="font-size:2rem; margin-bottom:1rem;">Admin Dashboard</h1>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; gap:12px; flex-wrap:wrap;">
        <h2 style="font-size:1.25rem;">Pending Users</h2>
        <div>
            <a href="{{ route('admin.skills.index') }}" style="padding:8px 12px; background:#2563eb; color:#fff; border-radius:6px; text-decoration:none;">Manage Skills</a>
        </div>
    </div>
    <table style="width:100%; border-collapse: collapse; background:#fff; box-shadow:0 2px 8px #eee;">
        <thead>
            <tr style="background:#f7fafc;">
                <th style="border-bottom: 1px solid #ccc; text-align:left; padding:12px;">Name</th>
                <th style="border-bottom: 1px solid #ccc; text-align:left; padding:12px;">Username</th>
                <th style="border-bottom: 1px solid #ccc; text-align:left; padding:12px;">Email</th>
                <th style="border-bottom: 1px solid #ccc; text-align:left; padding:12px;">Skill</th>
                <th style="border-bottom: 1px solid #ccc; text-align:left; padding:12px;">Photo</th>
                <th style="border-bottom: 1px solid #ccc; text-align:left; padding:12px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendingUsers as $user)
                <tr>
                    <td style="padding:12px;">
                        {{ $user->firstname }} {{ $user->middlename }} {{ $user->lastname }}
                    </td>
                    <td style="padding:12px;">{{ $user->username }}</td>
                    <td style="padding:12px;">{{ $user->email }}</td>
                    <td style="padding:12px;">{{ optional($user->skill)->name ?? '—' }}</td>
                    <td style="padding:12px;">
                        @if($user->photo)
                            <a href="{{ asset('storage/' . $user->photo) }}" target="_blank">
                                <img src="{{ asset('storage/' . $user->photo) }}" alt="User Photo" style="width:48px; height:48px; object-fit:cover; border-radius:6px; border:1px solid #ddd;">
                            </a>
                        @else
                            <span style="color:#888;">No photo</span>
                        @endif
                    </td>
                    <td style="padding:12px;">
                        <a href="{{ route('admin.user.show', $user->id) }}" style="padding:6px 16px; background:#4299e1; color:#fff; border:none; border-radius:4px; text-decoration:none; margin-right:6px;">
                            View
                        </a>
                        <form action="{{ route('admin.approve', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" style="padding:6px 16px; background:#38b2ac; color:#fff; border:none; border-radius:4px; cursor:pointer; margin-right:4px;">
                                Approve
                            </button>
                        </form>
                        <form action="{{ route('admin.reject', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" style="padding:6px 16px; background:#e53e3e; color:#fff; border:none; border-radius:4px; cursor:pointer;">
                                Reject
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="padding:12px; text-align:center; color:#888;">No pending users.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</main>
@endsection