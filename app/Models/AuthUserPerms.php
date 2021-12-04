<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class AuthUserPerms extends Model
{
    // use HasFactory;

    protected $table = 'user_role_levels';

    public $timestamps  = false;

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id')->select('id', 'username');
    }
    
    public function role()
    {
        // return $this->belongsTo(UserRoles::class, 'role_id')->select('id', 'role_name as name', 'dept_id');
        return $this->belongsTo(UserRoles::class, 'role_id')->select('id', 'role_name as name');
    }
   

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'user_id',
        'role_id',
        'role_ids_obj',  
    ];

    protected $tableColumnList = [
        'data_user_id' => 'data_user_id',
        'id' => 'id',
        'user_id' => 'user_id',
        'role_id' => 'role_id'
    ];

    protected $otherColumnList = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Scope a query to only include active records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_enable', '!=', Constant::RecordType['DELETED']);
    }

    /**
     * Set column Name with Actual name.
     * 
     * @param string $request
     * @return array
     */
    public function filterColumns(Request $request, $method = null)
    {
        if ($method == null) {
            $method = $request->method();
        }

        $columnList = $this->tableColumnList + $this->otherColumnList;

        Utilities::filterColumnsModel($request, $columnList, $method);
    }

    /**
     * Get column for ordering after varification.
     * 
     * @param string $field
     * @return string[]|array|string
     */
    public function getOrderColumn($field)
    {
        $columnList = $this->tableColumnList + $this->otherColumnList;

        foreach ($columnList as $key => $value) {
            if ($key === $field)
                return $key;
        }

        return "id";
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules($request, $method = null)
    {
        if ($method == null) {
            $method = $request->method();
        }

        $rules = [];

        $rules = match ($method) {
            'POST' => [
                'user_id'         => 'required|integer',
                'organization_id' => 'required|array',
                'role_id'         => 'required|array',
                'designation_id'  => 'required|integer',
                'department_id'   => 'required|integer',
                'countries_id'   => 'array|nullable',
                'state_id'       => 'array|nullable',
                'region_id'      => 'array|nullable',
                'city_id'        => 'array|nullable',
                'campus_id'      => 'array|nullable',
                
            ],
            'GET_ONE' => [
                'id' => 'required|integer'
                // 'fields' => ''
            ],
            'GET_ALL' => [
                // 'fields' => ''
            ]
        };

        return $rules;
    }

    /**
     * Get the validation custom messages.
     *
     * @return array
     */
    public function messages($request, $method = null)
    {
        if ($method == null) {
            $method = $request->method();
        }

        $messages = [];

        $commonMessages = [
            'user_id.required' => [
                "code" => 10107,
                "message" => "Please provide user id."
            ],
            'user_id.integer' => [
                "code" => 10106,
                "message" => "User id must be an integer."
            ],
            
            
        ];

        $idMessages = [
            'id.required' => [
                "code" => 10107,
                "message" => "Please provide user id."
            ],
            'id.integer' => [
                "code" => 10106,
                "message" => "Id must be an integer."
            ]
        ];

        $messages = match ($method) {
            'POST' => $commonMessages,
            'GET_ONE' => $idMessages,
            'GET_ALL' => $messages = []
        };

        return $messages;
    }
}
