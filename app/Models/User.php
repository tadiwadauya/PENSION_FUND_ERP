<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'extension',
        'address',
        'gender',
        'dob',
        'job_title',
        'grade',
        'department_id',
        'section_id',
        'supervisor_id',
        'reviewer_id',
        'is_admin',
        'is_hr',
        'is_ceo',
        'is_head_of_department',
        'is_head_of_section',
        'must_change_password',
        'otp_plain',
        'password_changed_at',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp_plain',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'dob' => 'date',
            'password_changed_at' => 'datetime',
            'must_change_password' => 'boolean',
            'is_admin' => 'boolean',
            'is_hr' => 'boolean',
            'is_ceo' => 'boolean',
            'is_head_of_department' => 'boolean',
            'is_head_of_section' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function managedDepartment()
    {
        return $this->hasOne(Department::class, 'head_user_id');
    }

    public function managedSections()
    {
        return $this->hasMany(Section::class, 'head_user_id');
    }

    public function isManagement(): bool
    {
        return $this->is_ceo || $this->is_head_of_department || $this->is_head_of_section;
    }

    public function fullName(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }
}