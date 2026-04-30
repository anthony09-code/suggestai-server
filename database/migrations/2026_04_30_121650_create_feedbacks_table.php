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
        Schema::create("feedbacks", function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table
                ->foreignUlid("student_id")
                ->constrained("students")
                ->cascadeOnDelete();
            $table
                ->foreignUlid("office_id")
                ->constrained("offices")
                ->cascadeOnDelete();
            $table->text("raw_text");
            $table
                ->enum("submission_method", ["qr_code", "manual_pick"])
                ->default("manual_pick");
            $table
                ->enum("status", ["pending", "processed"])
                ->default("pending");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("feedbacks");
    }
};
