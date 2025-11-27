<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ForumGroupSeeder::class,
            TagSeeder::class,
            TaskBoardSeeder::class,
            TaskSeeder::class,
            NoteSeeder::class,
            TimerSeeder::class,
            ForumPostSeeder::class,
            ForumReplySeeder::class,
            PostGroupSeeder::class,      // Group posts
            ReplyPostGroupSeeder::class,  // Replies to group posts
            AttachmentSeeder::class,
        ]);
    }
}
