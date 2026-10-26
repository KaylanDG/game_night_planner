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
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->string('status')->default('planned')->index()->after('title');
        });

        Schema::table('game_session', function (Blueprint $table) {
            $table->boolean('was_played')->default(false)->after('suggested_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_session', function (Blueprint $table) {
            $table->dropColumn('was_played');
        });

        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
