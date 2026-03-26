<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'name',
        'game',
        'start_date',
        'max_participants',
        'format',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'registrations')
                    ->withPivot('status', 'registered_at');
    }

    public function bracket(): HasOne
    {
        return $this->hasOne(Bracket::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class);
    }

    public function hasStartedMatches(): bool
    {
        return $this->matches()->where('status', '!=', 'scheduled')->exists();
    }
    public function scopeGame($query, $game)
    {
        if ($game) {
            return $query->where('game', $game);
        }
        return $query;
    }

    public function scopeStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }
}
