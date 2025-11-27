<?php

namespace App\Services;

use App\Models\ForumGroup;
use App\Models\ForumPost;
use App\Models\ForumReply;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ForumStatsService
{
    /**
     * Get overall forum statistics.
     *
     * @return array
     */
    public function getForumStats(): array
    {
        return [
            'total_groups' => ForumGroup::count(),
            'total_posts' => ForumPost::count(),
            'total_replies' => ForumReply::count(),
            'total_users' => User::has('forumPosts', '>', 0)
                ->orHas('forumReplies', '>', 0)
                ->count(),
            'new_today' => [
                'posts' => ForumPost::whereDate('created_at', today())->count(),
                'replies' => ForumReply::whereDate('created_at', today())->count(),
                'users' => User::whereDate('created_at', today())->count(),
            ],
        ];
    }

    /**
     * Get statistics for a specific group.
     *
     * @param  \App\Models\ForumGroup  $group
     * @return array
     */
    public function getGroupStats(ForumGroup $group): array
    {
        return [
            'total_posts' => $group->posts()->count(),
            'total_replies' => $group->replies()->count(),
            'total_members' => $group->members()->count(),
            'active_members' => $group->members()
                ->whereHas('forumPosts', function ($query) use ($group) {
                    $query->where('group_id', $group->id);
                })
                ->orWhereHas('forumReplies', function ($query) use ($group) {
                    $query->where('group_id', $group->id);
                })
                ->distinct()
                ->count('users.id'),
            'new_today' => [
                'posts' => $group->posts()->whereDate('created_at', today())->count(),
                'replies' => $group->replies()->whereDate('created_at', today())->count(),
                'members' => $group->members()
                    ->wherePivot('created_at', '>=', today())
                    ->count(),
            ],
        ];
    }

    /**
     * Get the most active users in the forum.
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function getMostActiveUsers(int $limit = 10): Collection
    {
        return User::select('users.*')
            ->withCount(['forumPosts', 'forumReplies'])
            ->orderByRaw('forum_posts_count + forum_replies_count DESC')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                $user->activity_score = $user->forum_posts_count + $user->forum_replies_count;
                return $user;
            });
    }

    /**
     * Get the most popular groups.
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function getPopularGroups(int $limit = 5): Collection
    {
        return ForumGroup::withCount(['posts', 'replies', 'members'])
            ->orderByRaw('posts_count + replies_count DESC')
            ->limit($limit)
            ->get()
            ->map(function ($group) {
                $group->activity_score = $group->posts_count + $group->replies_count;
                return $group;
            });
    }

    /**
     * Get the latest activity in the forum.
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function getLatestActivity(int $limit = 10): Collection
    {
        $posts = ForumPost::with(['user', 'group'])
            ->select('id', 'user_id', 'group_id', 'title', 'created_at')
            ->selectRaw("'post' as type");

        $replies = ForumReply::with(['user', 'post.group'])
            ->select('id', 'user_id', 'forum_post_id', 'content', 'created_at')
            ->selectRaw("NULL as title, 'reply' as type");

        return $posts->union($replies)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                if ($item->type === 'post') {
                    $item->url = route('forum.posts.show', [
                        'group' => $item->group->slug,
                        'post' => $item->id,
                        'slug' => $item->slug,
                    ]);
                } else {
                    $item->url = route('forum.posts.show', [
                        'group' => $item->post->group->slug,
                        'post' => $item->post_id,
                        'slug' => $item->post->slug,
                    ]) . '#reply-' . $item->id;
                }
                return $item;
            });
    }

    /**
     * Get activity trends over time.
     *
     * @param  int  $days
     * @return array
     */
    public function getActivityTrends(int $days = 30): array
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        // Initialize date range with all dates set to 0
        $dates = collect();
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $dates[$dateKey] = [
                'date' => $dateKey,
                'posts' => 0,
                'replies' => 0,
            ];
            $currentDate->addDay();
        }

        // Get posts count by date
        $posts = ForumPost::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Get replies count by date
        $replies = ForumReply::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Merge the counts into the dates collection
        $activity = $dates->map(function ($item) use ($posts, $replies) {
            $date = $item['date'];
            $item['posts'] = $posts[$date] ?? 0;
            $item['replies'] = $replies[$date] ?? 0;
            $item['total'] = $item['posts'] + $item['replies'];
            return $item;
        })->values();

        return [
            'labels' => $activity->pluck('date'),
            'datasets' => [
                [
                    'label' => 'Posts',
                    'data' => $activity->pluck('posts'),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Replies',
                    'data' => $activity->pluck('replies'),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                ]
            ]
        ];
    }


    /**
     * Get user participation statistics.
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    public function getUserParticipationStats(User $user): array
    {
        $posts = $user->forumPosts()
            ->select(DB::raw('COUNT(*) as count, DATE(created_at) as date'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $replies = $user->forumReplies()
            ->select(DB::raw('COUNT(*) as count, DATE(created_at) as date'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Merge posts and replies by date
        $dates = $posts->merge($replies)->keys()->unique()->sort();

        $data = [];
        foreach ($dates as $date) {
            $data[] = [
                'date' => $date,
                'posts' => $posts[$date] ?? 0,
                'replies' => $replies[$date] ?? 0,
                'total' => ($posts[$date] ?? 0) + ($replies[$date] ?? 0),
            ];
        }

        return [
            'total_posts' => $user->forumPosts()->count(),
            'total_replies' => $user->forumReplies()->count(),
            'groups_participated' => $user->forumGroups()->count(),
            'activity_by_day' => $data,
            'most_active_group' => $user->forumGroups()
                ->withCount(['posts' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }])
                ->orderBy('posts_count', 'desc')
                ->first(),
        ];
    }

    /**
     * Get the busiest times of day for the forum.
     *
     * @return array
     */
    public function getBusiestTimes(): array
    {
        $posts = ForumPost::select(DB::raw('HOUR(created_at) as hour'))
            ->selectRaw('COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $replies = ForumReply::select(DB::raw('HOUR(created_at) as hour'))
            ->selectRaw('COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Initialize all hours with 0
        $hours = array_fill(0, 24, [
            'hour' => 0,
            'posts' => 0,
            'replies' => 0,
            'total' => 0,
        ]);

        // Fill in the actual counts
        foreach (array_keys($hours) as $hour) {
            $hours[$hour]['hour'] = $hour;
            $hours[$hour]['posts'] = $posts[$hour] ?? 0;
            $hours[$hour]['replies'] = $replies[$hour] ?? 0;
            $hours[$hour]['total'] = $hours[$hour]['posts'] + $hours[$hour]['replies'];
        }

        return [
            'by_hour' => $hours,
            'busiest_hour' => array_search(max(array_column($hours, 'total')), array_column($hours, 'total')),
            'average_posts_per_day' => ForumPost::count() / max(1, ForumPost::select(DB::raw('DATEDIFF(NOW(), MIN(created_at)) as days'))->first()->days),
            'average_replies_per_day' => ForumReply::count() / max(1, ForumReply::select(DB::raw('DATEDIFF(NOW(), MIN(created_at)) as days'))->first()->days),
        ];
    }
}
