<?php

namespace App\Providers;

use App\Models\Note;
use App\Models\Task;
use App\Policies\NotePolicy;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        'App\Models\ForumGroup' => 'App\Policies\ForumGroupPolicy',
        'App\Models\ForumPost' => 'App\Policies\ForumPostPolicy',
        'App\Models\ForumReply' => 'App\Policies\ForumReplyPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
