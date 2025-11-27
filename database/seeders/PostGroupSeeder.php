<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if there are any groups and users
        if (DB::table('forum_groups')->count() === 0 || DB::table('users')->count() === 0) {
            $this->command->info('No groups or users found. Skipping post group seeding.');
            return;
        }

        $groupIds = DB::table('forum_groups')->pluck('id')->toArray();
        $userIds = DB::table('users')->pluck('id')->toArray();

        $posts = [
            [
                'content' => 'Welcome to our study group! Let\'s share knowledge and help each other grow.',
                'media_type' => null,
                'media_path' => null,
                'media_name' => null,
                'media_size' => null,
            ],
            [
                'content' => 'Check out this interesting article I found about our topic!',
                'media_type' => 'document',
                'media_path' => 'documents/article.pdf',
                'media_name' => 'Interesting Article.pdf',
                'media_size' => '2.5MB',
            ],
            [
                'content' => 'Here\'s a quick summary of what we discussed in our last meeting.',
                'media_type' => 'image',
                'media_path' => 'images/meeting_notes.jpg',
                'media_name' => 'Meeting Notes.jpg',
                'media_size' => '1.2MB',
            ],
        ];

        foreach ($posts as $post) {
            DB::table('post_group')->insert([
                'group_id' => $groupIds[array_rand($groupIds)],
                'user_id' => $userIds[array_rand($userIds)],
                'content' => $post['content'],
                'media_type' => $post['media_type'],
                'media_path' => $post['media_path'],
                'media_name' => $post['media_name'],
                'media_size' => $post['media_size'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Post group data seeded successfully!');
    }
}
