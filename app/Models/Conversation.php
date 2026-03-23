<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'avatar_path',
        'is_group',
        'created_by',
        'last_message_at',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'avatar_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_group' => 'boolean',
            'last_message_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Conversation $conversation): void {
            if (! empty($conversation->avatar_path)) {
                Storage::disk('public')->delete($conversation->avatar_path);
            }
        });
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! empty($this->avatar_path) && Storage::disk('public')->exists($this->avatar_path)) {
                return asset('storage/'.ltrim($this->avatar_path, '/'));
            }

            return null;
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
