<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $fillable = [
        'name', 'path', 'ram', 'ram_unit', 'jar_file', 'start_command',
        'type', 'port', 'max_players', 'java_args', 'pc_ip', 'pc_mac',
        'auto_start', 'auto_restart', 'restart_time', 'backup_enabled',
        'backup_interval', 'notify_stop', 'notify_message',
    ];

    protected function casts(): array
    {
        return [
            'auto_start' => 'boolean',
            'auto_restart' => 'boolean',
            'backup_enabled' => 'boolean',
            'notify_stop' => 'boolean',
        ];
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'vanilla' => '🗺️', 'paper' => '📄', 'spigot' => '⚡',
            'purpur' => '🟣', 'forge' => '🔥', 'fabric' => '📦',
            default => '🗺️',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'vanilla' => '#22c55e', 'paper' => '#f59e0b', 'spigot' => '#3b82f6',
            'purpur' => '#a855f7', 'forge' => '#ef4444', 'fabric' => '#ec4899',
            default => '#22c55e',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'vanilla' => 'Vanilla', 'paper' => 'Paper', 'spigot' => 'Spigot',
            'purpur' => 'Purpur', 'forge' => 'Forge', 'fabric' => 'Fabric',
            default => ucfirst($this->type),
        };
    }

    public function getRamDisplayAttribute(): string
    {
        return $this->ram . $this->ram_unit;
    }
}