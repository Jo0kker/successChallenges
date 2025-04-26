<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'name',
        'description',
        'status',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function challenges()
    {
        return $this->hasMany(Challenge::class);
    }

    public function canAddChallenges(): bool
    {
        return $this->status === 'active';
    }

    public function canChangeStatus(string $newStatus): bool
    {
        if ($this->status === 'completed') {
            return false;
        }

        if ($this->status === 'active' && $newStatus === 'pending') {
            return false;
        }

        return true;
    }

    public function setStatus(string $status): void
    {
        if (!$this->canChangeStatus($status)) {
            throw new \InvalidArgumentException("Transition de statut non autorisée");
        }

        $this->status = $status;

        if ($status === 'active') {
            $this->start_date = now();
        } elseif ($status === 'completed') {
            $this->end_date = now();
        }

        $this->save();
    }
}
