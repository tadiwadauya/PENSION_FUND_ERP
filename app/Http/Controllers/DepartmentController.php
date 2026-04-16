<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index()
    {
        $departments = Department::with(['head', 'reportsTo'])->latest()->paginate(10);

        return view('departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        $users = User::orderBy('first_name')->get();

        return view('departments.create', compact('users'));
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
            'head_user_id' => ['nullable', 'exists:users,id'],
            'reports_to_user_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $department = Department::create([
            'name' => $request->name,
            'head_user_id' => $request->head_user_id,
            'reports_to_user_id' => $request->reports_to_user_id,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($department->head_user_id) {
            $head = User::find($department->head_user_id);
            if ($head) {
                $head->department_id = $department->id;
                $head->is_head_of_department = true;
                $head->save();
            }
        }

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department)
    {
        $department->load(['head', 'reportsTo', 'sections', 'users']);

        return view('departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department)
    {
        $users = User::orderBy('first_name')->get();

        return view('departments.edit', compact('department', 'users'));
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department->id)],
            'head_user_id' => ['nullable', 'exists:users,id'],
            'reports_to_user_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($department->head_user_id && $department->head_user_id != $request->head_user_id) {
            $oldHead = User::find($department->head_user_id);
            if ($oldHead) {
                $oldHead->is_head_of_department = false;
                $oldHead->save();
            }
        }

        $department->update([
            'name' => $request->name,
            'head_user_id' => $request->head_user_id,
            'reports_to_user_id' => $request->reports_to_user_id,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($department->head_user_id) {
            $head = User::find($department->head_user_id);
            if ($head) {
                $head->department_id = $department->id;
                $head->is_head_of_department = true;
                $head->save();
            }
        }

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }
}