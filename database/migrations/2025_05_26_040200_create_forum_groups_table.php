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
        Schema::create('forum_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->default('fa-users');
            $table->string('cover_image')->nullable();
            $table->boolean('is_private')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Add group_id to forum_posts table
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->constrained('forum_groups')->onDelete('cascade');
        });

        // Create group members table
        Schema::create('forum_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('forum_groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['member', 'moderator', 'admin'])->default('member');
            $table->timestamps();

            $table->unique(['group_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });

        Schema::dropIfExists('forum_group_members');
        Schema::dropIfExists('forum_groups');
    }
};
