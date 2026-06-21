<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('server_id')->constrained()->onDelete('cascade');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_start')->default(false);
            $table->boolean('can_stop')->default(false);
            $table->boolean('can_console')->default(false);
            $table->boolean('can_files')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'server_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};