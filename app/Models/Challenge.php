<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bet_amount',
        'failed_by_type',
        'failed_by_id',
        'season_id',
    ];

    protected $casts = [
        'bet_amount' => 'decimal:2',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function failedBy(): MorphTo
    {
        return $this->morphTo();
    }

    public function participants(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'participant', 'challenge_participants')
            ->withPivot('participant_type', 'participant_id');
    }

    public function guestParticipants(): MorphToMany
    {
        return $this->morphedByMany(GuestParticipant::class, 'participant', 'challenge_participants')
            ->withPivot('participant_type', 'participant_id');
    }

    public function getAllParticipants()
    {
        $userParticipants = $this->participants()->get();
        $guestParticipants = $this->guestParticipants()->get();

        return $userParticipants->concat($guestParticipants);
    }

    public function markAsFailedByUser(User $user): void
    {
        $this->update([
            'failed_by_type' => User::class,
            'failed_by_id' => $user->id
        ]);
    }

    public function markAsFailedByGuest(GuestParticipant $guest): void
    {
        $this->update([
            'failed_by_type' => GuestParticipant::class,
            'failed_by_id' => $guest->id
        ]);
    }
}
