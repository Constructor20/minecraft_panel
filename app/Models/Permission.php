<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'user_id', 'server_id', 'can_view', 'can_start', 'can_stop',
        'can_console', 'can_files',
    ];

    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
            'can_start' => 'boolean',
            'can_stop' => 'boolean',
            'can_console' => 'boolean',
            'can_files' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}