<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('score_entries', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        DB::table('rounds')->orderBy('id')->lazy()->each(function (object $round): void {
            $userId = DB::table('matches')->where('id', $round->match_id)->value('user_id');

            DB::table('rounds')->where('id', $round->id)->update(['user_id' => $userId]);
        });

        DB::table('score_entries')->orderBy('id')->lazy()->each(function (object $entry): void {
            $userId = DB::table('rounds')->where('id', $entry->round_id)->value('user_id');

            DB::table('score_entries')->where('id', $entry->id)->update(['user_id' => $userId]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('score_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('rounds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
