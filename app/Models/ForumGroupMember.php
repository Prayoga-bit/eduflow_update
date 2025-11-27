<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumGroupMember extends Model
{
    protected $table = 'forum_group_members';
    
    protected $fillable = [
        'user_id',
        'group_id',
        'role',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(ForumGroup::class);
    }
}
