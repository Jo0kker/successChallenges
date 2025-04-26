<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bet_amount',
        'failed_by',
        'season_id',
    ];

    protected $casts = [
        'bet_amount' => 'decimal:2',
    ];

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function failedBy()
    {
        return $this->belongsTo(User::class, 'failed_by');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'challenge_participants')
            ->withTimestamps();
    }

    public function markAsFailed(User $user)
    {
        $this->update(['failed_by' => $user->id]);
    }
}
