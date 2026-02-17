<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_contents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('contentable');
            $table->foreignUuid('student_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_contents');
    }
};
