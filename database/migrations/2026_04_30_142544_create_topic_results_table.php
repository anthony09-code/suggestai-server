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
        Schema::create("topic_results", function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table
                ->foreignUlid("feedback_id")
                ->constrained("feedbacks")
                ->cascadeOnDelete();
            $table
                ->foreignUlid("office_id")
                ->constrained("offices")
                ->cascadeOnDelete();
            $table
                ->foreignUlid("session_id")
                ->constrained("analysis_sessions")
                ->cascadeOnDelete();
            $table
                ->foreignUlid("topic_id")
                ->nullable()
                ->constrained("topics")
                ->nullOnDelete();
            $table->text("cleaned_text");
            $table->text("translated_text")->nullable();
            $table->text("summary");
            $table->float("confidence_score")->default(0);
            $table->timestamp("processed_at")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("topic_results");
    }
};
