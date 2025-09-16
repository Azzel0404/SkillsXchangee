@extends('layouts.app')

@section('content')
<main style="padding:32px;">
    <h1 style="font-size:2rem; margin-bottom:1rem;">Manage Skills</h1>

    @if(session('success'))
        <div style="background:#def7ec; color:#03543f; padding:10px 12px; border-radius:6px; margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div id="error-message" style="background:#fef2f2; border:1px solid #fecaca; color:#dc2626; padding:12px 16px; border-radius:6px; margin-bottom:16px; position:relative;">
            <button onclick="closeErrorMessage()" style="position:absolute; top:8px; right:8px; background:none; border:none; color:#dc2626; font-size:18px; cursor:pointer; padding:0; width:20px; height:20px; display:flex; align-items:center; justify-content:center;">×</button>
            <div style="font-weight:600; margin-bottom:8px; padding-right:20px;">⚠️ Please fix the following errors:</div>
            <ul style="margin:0; padding-left:20px; padding-right:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="background:#fff; padding:16px; border-radius:8px; box-shadow:0 2px 8px #eee; margin-bottom:24px;">
        <div style="background:#f0f9ff; border:1px solid #0ea5e9; border-radius:6px; padding:12px; margin-bottom:16px;">
            <p style="margin:0; color:#0c4a6e; font-size:0.875rem;">
                <strong>Note:</strong> Skill names will be automatically formatted (proper case, single spaces). Duplicate skills are not allowed.
            </p>
        </div>
        <form method="POST" action="{{ route('admin.skill.store') }}" style="display:flex; gap:12px; flex-wrap:wrap;">
            @csrf
            <div style="flex:1 1 240px;">
                <label for="name" style="display:block; font-weight:600; margin-bottom:4px;">Skill Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required style="width:100%; padding:10px; border:1px solid {{ $errors->has('name') ? '#dc2626' : '#ddd' }}; border-radius:6px;" placeholder="e.g., Web Development" />
                @error('name')<div style="color:#dc2626; font-size:0.875rem; margin-top:4px; font-weight:500;">⚠️ {{ $message }}</div>@enderror
            </div>
            <div style="flex:1 1 240px;">
                <label for="category" style="display:block; font-weight:600; margin-bottom:4px;">Category</label>
                <input id="category" name="category" type="text" value="{{ old('category') }}" required style="width:100%; padding:10px; border:1px solid {{ $errors->has('category') ? '#dc2626' : '#ddd' }}; border-radius:6px;" placeholder="e.g., IT" />
                @error('category')<div style="color:#dc2626; font-size:0.875rem; margin-top:4px; font-weight:500;">⚠️ {{ $message }}</div>@enderror
            </div>
            <div style="align-self:end;">
                <button type="submit" style="padding:10px 16px; background:#2563eb; color:#fff; border:none; border-radius:6px; cursor:pointer;">Add Skill</button>
            </div>
        </form>
    </div>

    <table style="width:100%; border-collapse: collapse; background:#fff; box-shadow:0 2px 8px #eee;">
        <thead>
        <tr style="background:#f7fafc;">
            <th style="border-bottom: 1px solid #ccc; text-align:left; padding:12px;">Name</th>
            <th style="border-bottom: 1px solid #ccc; text-align:left; padding:12px;">Category</th>
            <th style="border-bottom: 1px solid #ccc; text-align:left; padding:12px;">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($skills as $skill)
            <tr>
                <td style="padding:12px;">{{ $skill->name }}</td>
                <td style="padding:12px;">{{ $skill->category }}</td>
                <td style="padding:12px;">
                    <form method="POST" action="{{ route('admin.skill.delete', $skill->skill_id) }}" onsubmit="return confirm('Delete this skill?');" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="padding:6px 12px; background:#ef4444; color:#fff; border:none; border-radius:4px; cursor:pointer;">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" style="padding:12px; text-align:center; color:#888;">No skills found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</main>

<script>
function closeErrorMessage() {
    const errorMessage = document.getElementById('error-message');
    if (errorMessage) {
        errorMessage.style.display = 'none';
    }
}
</script>

@endsection


