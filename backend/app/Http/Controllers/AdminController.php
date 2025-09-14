<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Skill;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $pendingUsers = User::where('is_verified', false)->get();
        return view('admin.dashboard', compact('pendingUsers'));
    }

    public function approve(User $user)
    {
        $user->is_verified = true;
        $user->save();
        return redirect()->route('admin.dashboard')->with('success', 'User approved!');
    }
    
    public function reject(User $user)
    {
        $user->is_verified = false;
        $user->save();
        return redirect()->route('admin.dashboard')->with('success', 'User rejected!');
    }

    public function show(User $user)
    {
        return view('admin.user_show', compact('user'));
    }

    public function skillsIndex()
    {
        $skills = Skill::orderBy('category')->orderBy('name')->get();
        return view('admin.skills.index', compact('skills'));
    }

    public function createSkill()
    {
        return view('admin.skills.create');
    }

    public function storeSkill(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:100'],
        ]);

        Skill::create($validated);

        return redirect()->route('admin.skills.index')->with('success', 'Skill added.');
    }

    public function deleteSkill(Skill $skill)
    {
        $skill->delete();
        return back()->with('success', 'Skill deleted.');
    }
}