<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'head_user_id',
        'reports_to_user_id',
        'reports_directly_to_ceo',
        'is_active',
        'description',
    ];

    protected $casts = [
        'reports_directly_to_ceo' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function head()
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    public function reportsTo()
    {
        return $this->belongsTo(User::class, 'reports_to_user_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}