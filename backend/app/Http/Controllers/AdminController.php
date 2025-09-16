<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Skill;
use App\Http\Requests\StoreSkillRequest;
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

    public function storeSkill(StoreSkillRequest $request)
    {
        try {
            $validated = $request->validated();

            Skill::create($validated);

            return redirect()->route('admin.skills.index')->with('success', 'Skill added successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database unique constraint violations
            if ($e->getCode() == 23000) { // MySQL duplicate entry error code
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['name' => 'A skill with this name already exists. Please choose a different name.']);
            }
            
            // Handle other database errors
            return redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'An error occurred while saving the skill. Please try again.']);
        } catch (\Exception $e) {
            // Handle any other unexpected errors
            return redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'An unexpected error occurred. Please try again.']);
        }
    }

    public function deleteSkill(Skill $skill)
    {
        $skill->delete();
        return back()->with('success', 'Skill deleted.');
    }
}