<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path', 512);
            $table->integer('ram')->default(4);
            $table->string('ram_unit', 1)->default('G');
            $table->string('jar_file')->default('server.jar');
            $table->string('start_command', 512)->nullable();
            $table->string('type', 50)->default('vanilla');
            $table->integer('port')->default(25565);
            $table->integer('max_players')->default(20);
            $table->string('java_args', 512)->default('-Xmx2G -Xms1G');
            $table->string('pc_ip', 45)->nullable();
            $table->string('pc_mac', 17)->nullable();
            $table->boolean('auto_start')->default(false);
            $table->boolean('auto_restart')->default(false);
            $table->string('restart_time', 50)->nullable();
            $table->boolean('backup_enabled')->default(false);
            $table->integer('backup_interval')->nullable()->unsigned();
            $table->boolean('notify_stop')->default(false);
            $table->text('notify_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};