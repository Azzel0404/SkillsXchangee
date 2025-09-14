@extends('layouts.app')

@section('content')
<main style="padding:32px;">
    <h1 style="font-size:2rem; margin-bottom:1rem;">Add Skill</h1>

    <div style="background:#fff; padding:16px; border-radius:8px; box-shadow:0 2px 8px #eee;">
        <form method="POST" action="{{ route('admin.skill.store') }}" style="display:grid; gap:12px; max-width:540px;">
            @csrf
            <div>
                <label for="name" style="display:block; font-weight:600; margin-bottom:4px;">Skill Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" />
                @error('name')<div style="color:#e53e3e; font-size:0.875rem;">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="category" style="display:block; font-weight:600; margin-bottom:4px;">Category</label>
                <input id="category" name="category" type="text" value="{{ old('category') }}" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" />
                @error('category')<div style="color:#e53e3e; font-size:0.875rem;">{{ $message }}</div>@enderror
            </div>
            <div>
                <button type="submit" style="padding:10px 16px; background:#2563eb; color:#fff; border:none; border-radius:6px; cursor:pointer;">Save</button>
                <a href="{{ route('admin.skills.index') }}" style="margin-left:8px;">Cancel</a>
            </div>
        </form>
    </div>
</main>
@endsection


