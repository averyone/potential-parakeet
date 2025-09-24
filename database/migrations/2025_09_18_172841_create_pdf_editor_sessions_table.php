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
        Schema::create('pdf_editor_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id')->unique();
            $table->unsignedBigInteger('pdf_template_id')->nullable();
            $table->string('pdf_path');
            $table->string('original_name')->nullable();
            $table->json('edits')->nullable(); // Store current edits
            $table->json('editor_state')->nullable(); // Store UI state (zoom, page, etc.)
            $table->integer('current_page')->default(1);
            $table->decimal('zoom_level', 5, 2)->default(100.00);
            $table->string('status')->default('active'); // active, saved, exported
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->foreign('pdf_template_id')->references('id')->on('pdf_templates')->onDelete('set null');
            $table->index(['session_id']);
            $table->index(['status']);
            $table->index(['last_activity_at']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_editor_sessions');
    }
};
