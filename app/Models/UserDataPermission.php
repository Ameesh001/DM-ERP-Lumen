<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class UserDataPermission extends Model
{
    // use HasFactory;

    protected $table = 'user_data_permission';

    public $timestamps  = false;

    public function user_role_levels()
    {
        return $this->belongsTo(AuthUser::class, 'user_id')->select('username');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'user_id',
        'hierarchy_level_id',
        'data_permissions_id',
        'created_at',
        'updated_at',
        
    ];

    protected $otherColumnList = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

}
