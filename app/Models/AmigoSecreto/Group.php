<?php

namespace App\Models\AmigoSecreto;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Group extends Model
{
    protected $fillable = [
        'name',
        'description',
        'budget_min',
        'budget_max',
        'event_date',
        'event_location',
        'collective_reveal_at',
    ];

    protected $casts = ['user_id' => 'integer', 'collective_reveal_at' => 'datetime'];

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

    public function scopeCreatedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
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

    public function collectiveRevealAt(): ?CarbonInterface
    {
        return $this->collective_reveal_at ?? ($this->event_date ? Carbon::parse($this->event_date)->startOfDay() : null);
    }

    public function canRevealCollectively(): bool
    {
        $release = $this->collectiveRevealAt();

        return $release && now()->greaterThanOrEqualTo($release);
    }

    public function withDrawLock(Closure $operation): mixed
    {
        return DB::transaction(fn () => $operation(static::lockForUpdate()->findOrFail($this->id)));
    }

    public function modifyRoster(Closure $operation): mixed
    {
        return $this->withDrawLock(function (Group $group) use ($operation) {
            if ($group->draws()->active()->exists()) {
                throw ValidationException::withMessages(['roster' => 'A lista de participantes e as restrições estão bloqueadas após o sorteio.']);
            }

            return $operation($group);
        });
    }
}
