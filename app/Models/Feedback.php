<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'content',
        'email',
        'name',
    ];

    protected $table = 'feedbacks';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
