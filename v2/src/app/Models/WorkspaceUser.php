<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkspaceUser extends Model
{
    protected $table = 'workspace_users';

    public $timestamps = false;

    protected $fillable = [
        'workspace_id',
        'user_id',
    ];
}
