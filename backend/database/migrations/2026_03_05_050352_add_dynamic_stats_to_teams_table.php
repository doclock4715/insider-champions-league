<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('teams', function (Blueprint $table) {
        // Add new columns for dynamic stats
        $table->float('attack_strength')->default(1.0);
        $table->float('defense_strength')->default(1.0);
        $table->integer('form')->default(0); // Can be positive or negative
        
        $table->dropColumn('strength');
    });
}

public function down(): void
{
    Schema::table('teams', function (Blueprint $table) {
        $table->dropColumn(['attack_strength', 'defense_strength', 'form']);
        $table->integer('strength');
    });
}
};
