<?php

namespace App\Models\AmigoSecreto;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Group extends Model
{
    protected $fillable = [
        // 'user_id',
        'name',
        'description',
        'budget_min',
        'budget_max',
        'event_date',
        'event_location',
    ];

    public static function selectedYear(mixed $year): int
    {
        return is_string($year) && preg_match('/^[1-9][0-9]{3}$/D', $year)
            ? (int) $year
            : now()->year;
    }

    public function eventYear(): int
    {
        return $this->event_date
            ? (int) substr($this->event_date, 0, 4)
            : $this->created_at->year;
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where(function (Builder $query) use ($year) {
            $query->whereYear('event_date', $year)
                ->orWhere(fn (Builder $query) => $query->whereNull('event_date')->whereYear('created_at', $year));
        });
    }

    // 🔹 Um grupo pertence a um usuário (dono do grupo)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔹 Um grupo tem vários participantes
    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    // 🔹 Um grupo pode ter várias restrições
    public function restrictions()
    {
        return $this->hasMany(Restriction::class);
    }

    // 🔹 Um grupo pode ter vários sorteios
    public function draws()
    {
        return $this->hasMany(AsDraw::class);
    }

    // App\Models\AmigoSecreto\Group.php

    public function eventIsOver(): bool
    {
        return $this->event_date && now()->greaterThanOrEqualTo($this->event_date);
    }

}
