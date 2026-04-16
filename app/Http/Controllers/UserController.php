<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        $data = User::with(['department', 'section', 'supervisor', 'reviewer'])
            ->latest()
            ->paginate(15);

        return view('users.index', compact('data'))
            ->with('i', (request()->input('page', 1) - 1) * 15);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $sections = Section::with('department')->orderBy('name')->get();
        $users = User::orderBy('first_name')->get();

        return view('users.create', compact('departments', 'sections', 'users'));
    }

    /**
     * Store a newly created user.
     */
   public function store(Request $request)
{
    $request->validate([
        'username' => ['required', 'string', 'max:255', 'unique:users,username'],
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'mobile' => ['nullable', 'string', 'max:255'],
        'extension' => ['nullable', 'string', 'max:255'],
        'address' => ['nullable', 'string', 'max:255'],
        'gender' => ['nullable', 'in:male,female,other'],
        'dob' => ['nullable', 'date'],
        'job_title' => ['nullable', 'string', 'max:255'],
        'grade' => ['nullable', 'integer'],
        'department_id' => ['nullable', 'exists:departments,id'],
        'section_id' => ['nullable', 'exists:sections,id'],
        'supervisor_id' => ['nullable', 'exists:users,id'],
        'reviewer_id' => ['nullable', 'exists:users,id'],
        'is_admin' => ['nullable', 'boolean'],
        'is_hr' => ['nullable', 'boolean'],
        'is_ceo' => ['nullable', 'boolean'],
        'is_head_of_department' => ['nullable', 'boolean'],
        'is_head_of_section' => ['nullable', 'boolean'],
        'password' => ['required', 'confirmed', 'min:8'],
    ]);

    $otp = $this->generateOtp();

    $fullName = trim($request->first_name . ' ' . $request->last_name);

    $user = User::create([
        'name' => $fullName,
        'username' => $request->username,
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'email' => $request->email,
        'mobile' => $request->mobile,
        'extension' => $request->extension,
        'address' => $request->address,
        'gender' => $request->gender,
        'dob' => $request->dob,
        'job_title' => $request->job_title,
        'grade' => $request->grade,
        'department_id' => $request->department_id,
        'section_id' => $request->section_id,
        'supervisor_id' => $request->supervisor_id,
        'reviewer_id' => $request->reviewer_id,
        'is_admin' => $request->boolean('is_admin'),
        'is_hr' => $request->boolean('is_hr'),
        'is_ceo' => $request->boolean('is_ceo'),
        'is_head_of_department' => $request->boolean('is_head_of_department'),
        'is_head_of_section' => $request->boolean('is_head_of_section'),
        'password' => Hash::make($request->password),
        'must_change_password' => true,
        'otp_plain' => $otp,
    ]);

    return redirect()->route('users.index')
        ->with('success', 'User created successfully. One time password: ' . $otp);
}

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['department', 'section', 'supervisor', 'reviewer']);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $departments = Department::orderBy('name')->get();
        $sections = Section::with('department')->orderBy('name')->get();
        $users = User::where('id', '!=', $user->id)->orderBy('first_name')->get();

        return view('users.edit', compact('user', 'departments', 'sections', 'users'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
           
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'mobile' => ['nullable', 'string', 'max:255'],
            'extension' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:male,female,other'],
            'dob' => ['nullable', 'date'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'grade' => ['nullable', 'integer'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'reviewer_id' => ['nullable', 'exists:users,id'],
            'is_admin' => ['nullable', 'boolean'],
            'is_hr' => ['nullable', 'boolean'],
            'is_ceo' => ['nullable', 'boolean'],
            'is_head_of_department' => ['nullable', 'boolean'],
            'is_head_of_section' => ['nullable', 'boolean'],
            'reset_password' => ['nullable', 'boolean'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        $user->update([
    'name' => $fullName,
    'username' => $request->username,
    'first_name' => $request->first_name,
    'last_name' => $request->last_name,
    'email' => $request->email,
    'mobile' => $request->mobile,
    'extension' => $request->extension,
    'address' => $request->address,
    'gender' => $request->gender,
    'dob' => $request->dob,
    'job_title' => $request->job_title,
    'grade' => $request->grade,
    'department_id' => $request->department_id,
    'section_id' => $request->section_id,
    'supervisor_id' => $request->supervisor_id,
    'reviewer_id' => $request->reviewer_id,
    'is_admin' => $request->boolean('is_admin'),
    'is_hr' => $request->boolean('is_hr'),
    'is_ceo' => $request->boolean('is_ceo'),
    'is_head_of_department' => $request->boolean('is_head_of_department'),
    'is_head_of_section' => $request->boolean('is_head_of_section'),
]);

        $message = 'User updated successfully.';

        if ($request->boolean('reset_password')) {
            $newPassword = $request->filled('password') ? $request->password : $this->generateOtp();
            $otp = $newPassword;

            $user->password = Hash::make($newPassword);
            $user->otp_plain = $otp;
            $user->must_change_password = true;
            $user->password_changed_at = null;
            $user->save();

            $message .= ' New one time password: ' . $otp;
        }

        return redirect()->route('users.index')->with('success', $message);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Display deleted users.
     */
    public function deleted()
    {
        $users = User::onlyTrashed()->latest()->paginate(15);

        return view('users.deleted', compact('users'))
            ->with('i', (request()->input('page', 1) - 1) * 15);
    }

    /**
     * Restore a deleted user.
     */
    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('users.deleted')->with('success', 'User restored successfully.');
    }

    /**
     * Generate one time password.
     */
    private function generateOtp(): string
    {
        return (string) random_int(100000, 999999);
    }
}