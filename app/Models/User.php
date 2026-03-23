<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'avatar_path',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'is_admin',
    ];

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
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Whether this user may change a group conversation's photo (app admin or group creator, must be a member).
     */
    public function canManageGroupPhoto(Conversation $conversation): bool
    {
        if (! $conversation->is_group) {
            return false;
        }

        if (! $conversation->users()->where('users.id', $this->id)->exists()) {
            return false;
        }

        if ($this->is_admin) {
            return true;
        }

        return (int) $conversation->created_by === (int) $this->id;
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if (! empty($this->avatar_path) && Storage::disk('public')->exists($this->avatar_path)) {
                return asset('storage/'.ltrim($this->avatar_path, '/'));
            }

            return asset('images/avatar-placeholder.svg');
        });
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class)
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
}
