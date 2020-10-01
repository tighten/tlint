<?php

namespace App\Models;

class User extends Authenticatable implements MustVerifyEmail, ExportsPersonalData
{
    use Notifiable, HasApiTokens, HasEnums, UsesUUID, Recoverable, LogsActivity, CausesActivity, HasTransactionalCalls;

    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    protected static $logAttributes = [];
    protected $rememberTokenName = null;
    protected string $recoveryCodesName = 'two_factor_recovery_codes';
    protected $fillable = [];
    protected $hidden = [];
    protected $casts = [];
    protected $dates = [];
    protected $enums = [
        'gender' => GenderEnum::class,
    ];

    public function relation_one(): HasMany
    {
        return $this->hasMany(RelationOne::class);
    }

    public function relation_two(): HasMany
    {
        return $this->hasMany(RelationTwo::class);
    }

    public function relation_three(): HasMany
    {
        return $this->hasMany(RelationThree::class);
    }

    public function scopeWhereEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', Str::lower($email));
    }

    public function getNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = Str::lower($value);
    }

    public function setPasswordAttribute(string $value): void
    {
        if (Hash::info($value)['algo'] === null) {
            $value = Hash::make($value);
        }

        $this->attributes['password'] = $value;
    }

    public function routeNotificationForMail(Notification $notification = null)
    {
        if ($notification instanceof VerifyUpdatingEmailNotification) {
            return $notification->newEmail;
        }

        return $this->email;
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification($this));
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($this, $token));
    }

    public function hasTwoFactorAuthenticationEnabled(): bool
    {
        return $this->two_factor_secret !== null;
    }

    public function enableTwoFactorAuthentication(string $secret, array $recoveryCodes): bool
    {
        return $this
            ->setRecoveryCodes($recoveryCodes)
            ->forceFill([
                'two_factor_secret' => $secret,
                'two_factor_last_verified_at' => now(),
            ])
            ->save();
    }

    public function disableTwoFactorAuthentication(): bool
    {
        return $this->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_last_verified_at' => null,
        ])->save();
    }

    public function isValidOtp(string $otp): bool
    {
        if (! $this->hasTwoFactorAuthenticationEnabled()) {
            return false;
        }

        $google2fa = app(Google2FA::class);

        return $google2fa->verifyKeyNewer(
            $this->two_factor_secret,
            $otp,
            floor($this->two_factor_last_verified_at->timestamp / $google2fa->getKeyRegeneration())
        );
    }

    public function personalDataExportName(): string
    {
        return Str::random();
    }

    public function selectPersonalData(PersonalDataSelection $personalData): void
    {
        $personalData->add('user.json', []);
    }
}
