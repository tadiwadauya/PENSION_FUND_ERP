<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    /**
     * Display a listing of sections.
     */
    public function index()
    {
        $sections = Section::with(['department', 'head', 'reportsTo'])->latest()->paginate(10);

        return view('sections.index', compact('sections'));
    }

    /**
     * Show the form for creating a new section.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $users = User::orderBy('first_name')->get();

        return view('sections.create', compact('departments', 'users'));
    }

    /**
     * Store a newly created section.
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'head_user_id' => ['nullable', 'exists:users,id'],
            'reports_to_user_id' => ['nullable', 'exists:users,id'],
            'reports_directly_to_ceo' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $exists = Section::where('department_id', $request->department_id)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'name' => 'This section already exists under the selected department.',
            ])->withInput();
        }

        $section = Section::create([
            'department_id' => $request->department_id,
            'name' => $request->name,
            'head_user_id' => $request->head_user_id,
            'reports_to_user_id' => $request->reports_to_user_id,
            'reports_directly_to_ceo' => $request->boolean('reports_directly_to_ceo'),
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($section->head_user_id) {
            $head = User::find($section->head_user_id);
            if ($head) {
                $head->department_id = $section->department_id;
                $head->section_id = $section->id;
                $head->is_head_of_section = true;
                $head->save();
            }
        }

        return redirect()->route('sections.index')->with('success', 'Section created successfully.');
    }

    /**
     * Display the specified section.
     */
    public function show(Section $section)
    {
        $section->load(['department', 'head', 'reportsTo', 'users']);

        return view('sections.show', compact('section'));
    }

    /**
     * Show the form for editing the specified section.
     */
    public function edit(Section $section)
    {
        $departments = Department::orderBy('name')->get();
        $users = User::orderBy('first_name')->get();

        return view('sections.edit', compact('section', 'departments', 'users'));
    }

    /**
     * Update the specified section.
     */
    public function update(Request $request, Section $section)
    {
        $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'head_user_id' => ['nullable', 'exists:users,id'],
            'reports_to_user_id' => ['nullable', 'exists:users,id'],
            'reports_directly_to_ceo' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($section->head_user_id && $section->head_user_id != $request->head_user_id) {
            $oldHead = User::find($section->head_user_id);
            if ($oldHead) {
                $oldHead->is_head_of_section = false;
                $oldHead->save();
            }
        }

        $section->update([
            'department_id' => $request->department_id,
            'name' => $request->name,
            'head_user_id' => $request->head_user_id,
            'reports_to_user_id' => $request->reports_to_user_id,
            'reports_directly_to_ceo' => $request->boolean('reports_directly_to_ceo'),
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($section->head_user_id) {
            $head = User::find($section->head_user_id);
            if ($head) {
                $head->department_id = $section->department_id;
                $head->section_id = $section->id;
                $head->is_head_of_section = true;
                $head->save();
            }
        }

        return redirect()->route('sections.index')->with('success', 'Section updated successfully.');
    }

    /**
     * Remove the specified section.
     */
    public function destroy(Section $section)
    {
        $section->delete();

        return redirect()->route('sections.index')->with('success', 'Section deleted successfully.');
    }
}