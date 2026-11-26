<?php

namespace App\Models\AmigoSecreto;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class Participant extends Model
{
    protected $fillable = [
        'group_id',
        'name',
        'gender',
        'email',
        'whatsapp',
        'wishlist',
        'secret_key',
        'shared_gift_idea',
        'bought_gift',
        'photo',
        'last_access_at',
    ];

    protected $casts = [
        'invite_expires_at' => 'datetime',
        'first_revealed_at' => 'datetime',
        'draw_email_enabled' => 'boolean',
    ];

    protected $hidden = ['pwa_access_hash', 'secret_key', 'email', 'whatsapp', 'photo', 'wishlist'];

    public function hasActiveInvite(): bool
    {
        return filled($this->pwa_access_hash) && $this->invite_expires_at?->isFuture();
    }

    public function scopeWithActiveInvite($query)
    {
        return $query->whereNotNull('pwa_access_hash')->where('invite_expires_at', '>', now());
    }

    // 🔒 Criptografa ao salvar
    // public function setNameAttribute($value)
    // {
    //     $this->attributes['name'] = !empty($value) ? Crypt::encryptString($value) : null;
    // }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = !empty($value) ? Crypt::encryptString($value) : null;
    }

    public function getEmailAttribute(?string $value): ?string
    {
        if ($value === null || str_contains($value, '@') || !str_starts_with($value, 'eyJpdiI6')) {
            return $value;
        }

        try {
            $email = Crypt::decryptString($value);

            return str_contains($email, '@') ? $email : null;
        } catch (DecryptException) {
            return null;
        }
    }

    public function getWhatsappAttribute(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (str_starts_with($value, 'eyJpdiI6')) {
            try {
                $value = Crypt::decryptString($value);
            } catch (DecryptException) {
                return null;
            }
        }

        $number = trim($value);

        return preg_match('/^\+?[0-9 ().-]+$/D', $number) && preg_match('/[0-9]/', $number)
            ? $number
            : null;
    }

    // public function setWishlistAttribute($value)
    // {
    //     $this->attributes['wishlist'] = !empty($value) ? Crypt::encryptString($value) : null;
    // }

    // 🔧 Fallback para dados não criptografados (evita "The payload is invalid")
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        // Aplica fallback apenas em campos criptografados
        if (in_array($key, ['name', 'wishlist']) && !empty($value)) {
            // Detecta se o valor "parece" criptografado (base64 + prefixo esperado)
            $isEncrypted = is_string($value) && str_starts_with($value, 'eyJpdiI6');

            if ($isEncrypted) {
                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException $e) {
                    return $value; // Se falhar, retorna valor bruto
                } catch (\Exception $e) {
                    return $value;
                }
            }

            // Se não parecer criptografado, apenas retorna o valor original
            return $value;
        }

        return $value;
    }


    // 🔹 Relacionamentos
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function restrictions()
    {
        return $this->hasMany(Restriction::class);
    }

    public function givenAssignments()
    {
        return $this->hasMany(Assignment::class, 'giver_id');
    }

    public function activeAssignments()
    {
        return $this->givenAssignments()->whereHas(
            'draw', fn ($query) => $query->active()->where('group_id', $this->group_id)
        );
    }

    public function inviteScope(): string
    {
        return hash_hmac('sha256', $this->pwa_access_hash, config('app.key'));
    }

    public function receivedAssignments()
    {
        return $this->hasMany(Assignment::class, 'receiver_id');
    }

    // 👁️ Máscaras de exibição
    public function getMaskedNameAttribute(): string
    {
        return str_repeat('*', max(mb_strlen($this->name ?? ''), 5)); 
    }

    public function getMaskedWhatsappAttribute(): string
    {
        $phone = $this->whatsapp;
        if (!$phone) return '—';
        return substr($phone, 0, 4) . str_repeat('*', 5) . substr($phone, -2);
    }

    public function getMaskedEmailAttribute(): string
    {
        $email = $this->email;
        if (!$email || !str_contains($email, '@')) return '—';
        [$user, $domain] = explode('@', $email);
        return substr($user, 0, 2) . '***@' . $domain;
    }

    public function reveal()
    {
        return $this->hasOne(SecretReveal::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }

}
