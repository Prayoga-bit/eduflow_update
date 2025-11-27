<?php

namespace App\Services;

use App\Models\ForumGroup;
use App\Models\ForumPost;
use App\Models\ForumReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;

class ForumService
{
    /**
     * Create a new forum group.
     *
     * @param array $data
     * @param User $user
     * @param UploadedFile|null $bannerImage
     * @return ForumGroup
     */
    public function createGroup(array $data, User $user, ?UploadedFile $bannerImage = null): ForumGroup
    {
        return DB::transaction(function () use ($data, $user, $bannerImage) {
            $group = new ForumGroup([
                'name' => $data['name'],
                'slug' => $this->generateUniqueSlug($data['name'], 'forum_groups'),
                'description' => $data['description'],
                'is_public' => $data['is_public'] ?? true,
                'user_id' => $user->id,
            ]);

            if ($bannerImage) {
                $group->banner_image = $this->storeBannerImage($bannerImage);
            }

            $group->save();

            // Add the creator as an admin
            $group->members()->attach($user->id, ['role' => 'admin']);

            return $group;
        });
    }


    /**
     * Update a forum group.
     *
     * @param ForumGroup $group
     * @param array $data
     * @param UploadedFile|null $bannerImage
     * @return ForumGroup
     */
    public function updateGroup(ForumGroup $group, array $data, ?UploadedFile $bannerImage = null): ForumGroup
    {
        return DB::transaction(function () use ($group, $data, $bannerImage) {
            $group->name = $data['name'];
            $group->slug = $this->generateUniqueSlug($data['name'], 'forum_groups', $group->id);
            $group->description = $data['description'];
            $group->is_public = $data['is_public'] ?? $group->is_public;

            if ($bannerImage) {
                // Delete old banner if exists
                if ($group->banner_image) {
                    Storage::disk('public')->delete($group->banner_image);
                }
                $group->banner_image = $this->storeBannerImage($bannerImage);
            }

            $group->save();

            return $group;
        });
    }

    /**
     * Delete a forum group.
     *
     * @param ForumGroup $group
     * @return void
     */
    public function deleteGroup(ForumGroup $group): void
    {
        DB::transaction(function () use ($group) {
            // Delete banner image if exists
            if ($group->banner_image) {
                Storage::disk('public')->delete($group->banner_image);
            }

            // Delete all related data (posts, replies, etc.)
            $group->posts->each->delete();
            
            // Detach all members
            $group->members()->detach();
            
            // Delete the group
            $group->delete();
        });
    }

    /**
     * Store a banner image and return the path.
     *
     * @param UploadedFile $image
     * @return string
     */
    protected function storeBannerImage(UploadedFile $image): string
    {
        $path = 'forum/banners/' . Str::random(40) . '.' . $image->getClientOriginalExtension();
        
        // Resize and save the image
        $img = Image::make($image->getRealPath())
            ->fit(1200, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
        Storage::disk('public')->put($path, (string) $img->encode());
        
        return $path;
    }
    /**
     * Generate a unique slug.
     *
     * @param string $title
     * @param string $table
     * @param int|null $id
     * @return string
     */
    protected function generateUniqueSlug(string $title, string $table, ?int $id = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 2;

        while (
            DB::table($table)
                ->where('slug', $slug)
                ->when($id, function ($query) use ($id) {
                    return $query->where('id', '!=', $id);
                })
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
    /**
     * Search for forum posts.
     *
     * @param string $query
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchPosts(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return ForumPost::with(['user', 'group'])
            ->where('title', 'like', "%{$query}%")
            ->orWhere('content', 'like', "%{$query}%")
            ->latest()
            ->paginate($perPage);
    }
    /**
     * Get recent activity for a user.
     *
     * @param User $user
     * @param int $limit
     * @return array
     */
    public function getUserActivity(User $user, int $limit = 10): array
    {
        $posts = $user->forumPosts()
            ->with('group')
            ->latest()
            ->limit($limit)
            ->get();
            
        $replies = $user->forumReplies()
            ->with(['post', 'post.group'])
            ->latest()
            ->limit($limit)
            ->get();
            
        $activity = $posts->concat($replies)
            ->sortByDesc('created_at')
            ->take($limit);
            
        return $activity->values()->all();
    }
}
