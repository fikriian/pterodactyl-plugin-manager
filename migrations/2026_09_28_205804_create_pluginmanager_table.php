<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pluginmanager', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->text('curseforge_api_key')->nullable();
            $table->enum('default_platform', ['modrinth', 'curseforge', 'hangar', 'spigotmc'])->default('modrinth');
            $table->enum('default_results', [6, 12, 18, 24, 30, 36, 42, 48])->default(48);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pluginmanager');
    }
};