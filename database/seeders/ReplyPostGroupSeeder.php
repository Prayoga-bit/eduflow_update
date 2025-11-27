<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ReplyPostGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all post group IDs
        $postGroupIds = DB::table('post_group')->pluck('id')->toArray();
        
        // Get all user IDs
        $userIds = DB::table('users')->pluck('id')->toArray();
        
        if (empty($postGroupIds) || empty($userIds)) {
            $this->command->info('No posts or users found. Please seed users and posts first.');
            return;
        }
        
        $replies = [];
        
        // Create 50-100 random replies
        $replyCount = rand(50, 100);
        
        for ($i = 0; $i < $replyCount; $i++) {
            $postGroupId = $faker->randomElement($postGroupIds);
            $userId = $faker->randomElement($userIds);
            $createdAt = $faker->dateTimeBetween('-6 months', 'now');
            
            // 30% chance to have media
            $hasMedia = $faker->boolean(30);
            $mediaPath = null;
            $mediaType = null;
            $mediaName = null;
            $mediaSize = null;
            
            if ($hasMedia) {
                $mediaTypes = ['image', 'document', 'video'];
                $mediaType = $faker->randomElement($mediaTypes);
                $mediaName = 'sample_' . $faker->word . '.' . $faker->fileExtension;
                $mediaSize = $faker->randomNumber(6); // File size in bytes
                $mediaPath = 'uploads/replies/' . $faker->md5 . '.' . $faker->fileExtension;
            }
            
            $replies[] = [
                'post_group_id' => $postGroupId,
                'user_id' => $userId,
                'content' => $faker->paragraph(rand(1, 5)),
                'media_path' => $mediaPath,
                'media_type' => $mediaType,
                'media_name' => $mediaName,
                'media_size' => $mediaSize,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }
        
        // Insert all replies
        DB::table('reply_post_group')->insert($replies);
        
        $this->command->info('Reply post group data seeded successfully!');
    }
}
