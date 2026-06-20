<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("topic_results", function (Blueprint $table) {
            $table->text("cleaned_text")->nullable()->change();
            $table->char("session_id", 26)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("topic_results_v2", function (Blueprint $table) {
            //
        });
    }
};
