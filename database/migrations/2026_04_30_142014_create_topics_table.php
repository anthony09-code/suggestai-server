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
        Schema::create("topics", function (Blueprint $table) {
            $table->ulid("id")->primary();
            $table
                ->foreignUlid("office_id")
                ->constrained("offices")
                ->cascadeOnDelete();
            $table->string("label");
            $table->json("keywords");
            $table->integer("feedback_count")->default(0);
            $table->float("cluster_x")->nullable();
            $table->float("cluster_y")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("topics");
    }
};
