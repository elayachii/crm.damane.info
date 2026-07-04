<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasUuid;
use App\Support\AuthFlowTrace;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['agency_id', 'name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasUuid;
    use Notifiable;
    use SoftDeletes;

    /**
     * @return BelongsTo<Agency, User>
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        AuthFlowTrace::info('user.can_access_panel.before', [
            'panel_id' => $panel->getId(),
            'user_id' => $this->getKey(),
            'user_auth_identifier' => $this->getAuthIdentifier(),
            'agency_id' => $this->agency_id,
        ]);

        $canAccess = $this->agency()
            ->where('is_active', true)
            ->exists();

        AuthFlowTrace::info('user.can_access_panel.after', [
            'panel_id' => $panel->getId(),
            'user_id' => $this->getKey(),
            'user_auth_identifier' => $this->getAuthIdentifier(),
            'agency_id' => $this->agency_id,
            'can_access' => $canAccess,
        ]);

        return $canAccess;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
