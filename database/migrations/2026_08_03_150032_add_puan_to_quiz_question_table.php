<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('quiz_question', function (Blueprint $table) {
            $table->integer('puan')->nullable()->after('question_id');
        });
    }

    public function down()
    {
        Schema::table('quiz_question', function (Blueprint $table) {
            $table->dropColumn('puan');
        });
    }
};
