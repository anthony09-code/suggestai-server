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
        Schema::create("analysis_sessions", function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table
                ->foreignUlid("user_id")
                ->constrained("users")
                ->cascadeOnDelete();
            $table
                ->foreignUlid("office_id")
                ->constrained("offices")
                ->cascadeOnDelete();
            $table->integer("feedback_count");
            $table->integer("topic_count");
            $table
                ->enum("status", [
                    "pending",
                    "processing",
                    "completed",
                    "failed",
                ])
                ->default("pending");
            $table->date("date_from");
            $table->date("date_to");
            $table->timestamp("started_at")->nullable();
            $table->timestamp("completed_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("analysis_sessions");
    }
};
