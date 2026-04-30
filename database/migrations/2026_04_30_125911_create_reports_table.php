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
        Schema::create("reports", function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table
                ->foreignUlid("user_id")
                ->constrained("users")
                ->cascadeOnDelete();
            $table
                ->foreignUlid("office_id")
                ->constrained("offices")
                ->cascadeOnDelete();
            $table
                ->foreignUlid("session_id")
                ->constrained("analysis_sessions")
                ->cascadeOnDelete();
            $table->string("title");
            $table->enum("format", ["pdf", "print"])->default("pdf");
            $table->string("file_path")->nullable();
            $table->timestamp("generated_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("reports");
    }
};
