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
        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->uuid('announcementable_id')->nullable();
            $table->string('announcementable_type')->nullable();
            $table->index(['announcementable_id', 'announcementable_type']);
            $table->string('title');
            $table->text('content');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_custom')->default(false);
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
        Schema::dropIfExists('announcements');
    }
};
