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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('team_one_name');
            $table->string('team_two_name');
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->unsignedInteger('round_number');
            $table->unsignedInteger('team_one_score')->default(0);
            $table->unsignedInteger('team_two_score')->default(0);
            $table->string('winner')->nullable();
            $table->string('mils_team')->nullable();
            $table->string('status')->default('playing');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->unique(['match_id', 'round_number']);
        });

        Schema::create('score_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained()->cascadeOnDelete();
            $table->string('team');
            $table->unsignedSmallInteger('points');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('score_entries');
        Schema::dropIfExists('rounds');
        Schema::dropIfExists('matches');
    }
};
