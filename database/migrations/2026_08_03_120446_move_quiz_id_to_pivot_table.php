<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Mevcut soru-quiz ilişkilerini pivot tabloya kopyala
        DB::table('questions')->whereNotNull('quiz_id')->get()->each(function ($question) {
            DB::table('quiz_question')->insert([
                'quiz_id' => $question->quiz_id,
                'question_id' => $question->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
            $table->dropColumn('quiz_id');
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('quiz_id')->nullable()->constrained()->onDelete('cascade');
        });
    }
};
