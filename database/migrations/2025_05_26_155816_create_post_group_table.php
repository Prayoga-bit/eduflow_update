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
        Schema::create('post_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('forum_groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->string('media_path')->nullable();
            $table->string('media_type')->nullable()->comment('image, document, video, etc.');
            $table->string('media_name')->nullable();
            $table->string('media_size')->nullable();
            $table->timestamps();
            
            // Add index for better performance on group queries
            $table->index('group_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_group');
    }
};
