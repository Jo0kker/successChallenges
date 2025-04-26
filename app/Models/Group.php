<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'owner_id',
        'is_active',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function seasons()
    {
        return $this->hasMany(Season::class);
    }

    public function isOwner(User $user)
    {
        return $this->owner_id === $user->id;
    }

    public function isAdmin(User $user)
    {
        return $this->members()
            ->wherePivot('role', 'admin')
            ->where('users.id', $user->id)
            ->exists();
    }

    public function isMember(User $user): bool
    {
        return $this->members()->where('users.id', $user->id)->exists();
    }

    public function isModerator(User $user)
    {
        return $this->members()
            ->wherePivot('role', 'moderator')
            ->where('users.id', $user->id)
            ->exists();
    }

    public function canManage(User $user)
    {
        return $this->isOwner($user) || $this->isAdmin($user);
    }

    public function canManageMembers(User $user)
    {
        return $this->isOwner($user) || $this->isModerator($user);
    }
}
