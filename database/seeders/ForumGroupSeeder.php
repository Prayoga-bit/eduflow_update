<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ForumGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ForumGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users to be group creators and members
        $users = User::take(5)->get();
        
        if ($users->isEmpty()) {
            $this->call([UserSeeder::class]);
            $users = User::take(5)->get();
        }

        $groups = [
            [
                'name' => 'Web Development Enthusiasts',
                'description' => 'A group for web developers to share knowledge and collaborate on projects.',
                'icon' => 'fa-code',
                'is_private' => false,
            ],
            [
                'name' => 'Mobile App Developers',
                'description' => 'Discussion group for mobile app development across all platforms.',
                'icon' => 'fa-mobile-alt',
                'is_private' => false,
            ],
            [
                'name' => 'UI/UX Designers',
                'description' => 'For designers to share their work and get feedback.',
                'icon' => 'fa-paint-brush',
                'is_private' => false,
            ],
            [
                'name' => 'Data Science Community',
                'description' => 'Group for data scientists and machine learning enthusiasts.',
                'icon' => 'fa-chart-line',
                'is_private' => true,
            ],
            [
                'name' => 'DevOps Engineers',
                'description' => 'Discussion about CI/CD, cloud infrastructure, and DevOps practices.',
                'icon' => 'fa-server',
                'is_private' => false,
            ],
        ];

        foreach ($groups as $index => $groupData) {
            $creator = $users->get($index % $users->count(), $users->first());
            
            $group = ForumGroup::create([
                'name' => $groupData['name'],
                'slug' => Str::slug($groupData['name']),
                'description' => $groupData['description'],
                'icon' => $groupData['icon'],
                'is_private' => $groupData['is_private'],
                'created_by' => $creator->id,
            ]);

            // Add creator as admin
            $group->members()->attach($creator->id, [
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add some members to the group
            $memberUsers = $users->where('id', '!=', $creator->id)->random(rand(1, 3));
            
            foreach ($memberUsers as $member) {
                // Skip if already a member (shouldn't happen but just in case)
                if ($group->members()->where('user_id', $member->id)->exists()) {
                    continue;
                }
                
                $group->members()->attach($member->id, [
                    'role' => 'member',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // For private groups, make one member a moderator
            if ($groupData['is_private'] && $memberUsers->isNotEmpty()) {
                $moderator = $memberUsers->first();
                $group->members()->updateExistingPivot($moderator->id, [
                    'role' => 'moderator',
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
