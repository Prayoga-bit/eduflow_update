<?php

namespace App\Rules;

use App\Models\ForumGroup;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ForumGroupMember implements Rule
{
    /**
     * The forum group instance.
     *
     * @var \App\Models\ForumGroup
     */
    protected $group;

    /**
     * The required role for the member.
     *
     * @var string|null
     */
    protected $role;

    /**
     * Create a new rule instance.
     *
     * @param  \App\Models\ForumGroup|int|string  $group
     * @param  string|null  $role
     * @return void
     */
    public function __construct($group, ?string $role = null)
    {
        if (!$group instanceof ForumGroup) {
            $group = is_numeric($group) 
                ? ForumGroup::find($group)
                : ForumGroup::where('slug', $group)->first();
        }

        $this->group = $group;
        $this->role = $role;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // If no group found, fail the validation
        if (!$this->group) {
            return false;
        }

        $user = Auth::user();

        // If no user is authenticated, fail the validation
        if (!$user) {
            return false;
        }

        // Check if the user is a member of the group
        if (!$this->group->isMember($user)) {
            return false;
        }

        // If a specific role is required, check if the user has that role
        if ($this->role && !$this->group->hasMemberWithRole($user, $this->role)) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        if (!$this->group) {
            return 'The selected group does not exist.';
        }

        if ($this->role) {
            return 'You must be a ' . $this->role . ' of the group to perform this action.';
        }

        return 'You must be a member of the group to perform this action.';
    }

    /**
     * Require the user to be an admin of the group.
     *
     * @return $this
     */
    public function asAdmin()
    {
        $this->role = 'admin';
        return $this;
    }

    /**
     * Require the user to be a moderator of the group.
     *
     * @return $this
     */
    public function asModerator()
    {
        $this->role = 'moderator';
        return $this;
    }

    /**
     * Require the user to be a regular member of the group.
     *
     * @return $this
     */
    public function asMember()
    {
        $this->role = 'member';
        return $this;
    }

    /**
     * Dynamically handle method calls.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (str_starts_with($method, 'as')) {
            $role = strtolower(substr($method, 2));
            $this->role = $role;
            return $this;
        }

        throw new \BadMethodCallException("Method [{$method}] does not exist on [".static::class.'].');
    }
}
