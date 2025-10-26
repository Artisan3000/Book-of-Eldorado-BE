<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'avatar'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* ===================================================================
     *  RELATIONSHIPS
     * =================================================================== */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function instructorProfile(): HasOne
    {
        return $this->hasOne(Instructor::class);
    }

    /* ===================================================================
     *  ROLE CHECKERS
     * =================================================================== */
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isInstructor(): bool { return $this->role === 'instructor'; }
    public function isStudent(): bool { return $this->role === 'student'; }

    /* ===================================================================
     *  CUSTOM: assignRole()
     * =================================================================== */
    public function assignRole(string $role): void
    {
        $validRoles = ['student', 'instructor', 'admin'];
        if (!in_array($role, $validRoles)) {
            throw new \InvalidArgumentException("Invalid role: {$role}");
        }

        $this->role = $role;
        $this->save();

        // Auto-create profile if needed
        if ($role === 'student' && !$this->studentProfile) {
            $this->studentProfile()->create([]);
        }

        if ($role === 'instructor' && !$this->instructorProfile) {
            $this->instructorProfile()->create([]);
        }
    }
}