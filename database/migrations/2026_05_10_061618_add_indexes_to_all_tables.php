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
        Schema::table("students", function (Blueprint $table) {
            $table->index("email");
            $table->index("google_id");
            $table->index("is_active");
        });

        Schema::table("offices", function (Blueprint $table) {
            $table->index("is_active");
            $table->index("office_name");
            $table->index("access_link");
        });

        Schema::table("feedbacks", function (Blueprint $table) {
            $table->index("status");
            $table->index("created_at");

            $table->index(["office_id", "status"]);
            $table->index(["office_id", "created_at"]);
            $table->index(["student_id", "office_id"]);
        });

        Schema::table("analysis_sessions", function (Blueprint $table) {
            $table->index("status");
            $table->index("created_at");

            $table->index(["office_id", "status"]);
            $table->index(["office_id", "created_at"]);
        });

        Schema::table("topics", function (Blueprint $table) {
            $table->index("feedback_count");
            $table->index("created_at");

            $table->index(["office_id", "feedback_count"]);
        });

        Schema::table("topic_results", function (Blueprint $table) {
            $table->index("confidence_score");
            $table->index("processed_at");

            $table->index(["office_id", "topic_id"]);
            $table->index(["session_id", "topic_id"]);
            $table->index(["feedback_id", "topic_id"]);
        });

        Schema::table("reports", function (Blueprint $table) {
            $table->index("generated_at");
            $table->index("format");

            $table->index(["office_id", "generated_at"]);
            $table->index(["user_id", "generated_at"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("students", function (Blueprint $table) {
            $table->dropIndex(["email"]);
            $table->dropIndex(["google_id"]);
            $table->dropIndex(["is_active"]);
        });

        Schema::table("offices", function (Blueprint $table) {
            $table->dropIndex(["is_active"]);
            $table->dropIndex(["office_name"]);
            $table->dropIndex(["access_link"]);
        });

        Schema::table("feedbacks", function (Blueprint $table) {
            $table->dropIndex(["status"]);
            $table->dropIndex(["created_at"]);
            $table->dropIndex(["office_id", "status"]);
            $table->dropIndex(["office_id", "created_at"]);
            $table->dropIndex(["office_id", "language"]);
            $table->dropIndex(["office_id", "submission_method"]);
            $table->dropIndex(["student_id", "office_id"]);
        });

        Schema::table("analysis_sessions", function (Blueprint $table) {
            $table->dropIndex(["status"]);
            $table->dropIndex(["created_at"]);
            $table->dropIndex(["office_id", "status"]);
            $table->dropIndex(["office_id", "created_at"]);
        });

        Schema::table("topics", function (Blueprint $table) {
            $table->dropIndex(["feedback_count"]);
            $table->dropIndex(["created_at"]);
            $table->dropIndex(["office_id", "feedback_count"]);
            $table->dropIndex(["office_id", "session_id"]);
        });

        Schema::table("topic_results", function (Blueprint $table) {
            $table->dropIndex(["confidence_score"]);
            $table->dropIndex(["processed_at"]);
            $table->dropIndex(["office_id", "topic_id"]);
            $table->dropIndex(["session_id", "topic_id"]);
            $table->dropIndex(["feedback_id", "topic_id"]);
        });

        Schema::table("reports", function (Blueprint $table) {
            $table->dropIndex(["generated_at"]);
            $table->dropIndex(["format"]);
            $table->dropIndex(["office_id", "generated_at"]);
            $table->dropIndex(["user_id", "generated_at"]);
        });
    }
};
