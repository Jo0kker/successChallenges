<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'group_member_id',
        'is_winner',
        'amount_won',
    ];

    protected $casts = [
        'amount_won' => 'decimal:2',
        'has_paid' => 'boolean',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function groupMember(): BelongsTo
    {
        return $this->belongsTo(GroupMember::class);
    }

    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            GroupMember::class,
            'id',
            'id',
            'group_member_id',
            'user_id'
        );
    }

    public function markAsPaid()
    {
        $this->update(['has_paid' => true]);
    }
}
