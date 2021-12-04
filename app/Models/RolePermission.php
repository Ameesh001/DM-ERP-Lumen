<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{

    protected $table = 'auth_role_module_perms';

    public $timestamps  = false;

    // public function client()
    // {
    //     return $this->belongsTo(Organization::class, 'client_id')->select('id', 'org_name');
    // }
    
    public function UserRoles()
    {
        return $this->belongsTo(UserRoles::class, 'role_id')->select('id', 'role_name');
    }
    
    public function auth_module()
    {
        return $this->belongsTo(AuthModule::class, 'module_id')->where('is_enable', '=', 1)->orderBy('order', 'asc')->select('id', 'name as title', 'default_url as path', 'icon_class as icon', 'parent_id', 'have_child', 'sorting as order');
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        //'client_id',
        'role_id',
        'module_id',
        'route',
        'action'
    ];

    protected $tableColumnList = [
        'data_user_id' => 'data_user_id',
       // 'client_id' => 'client_id',
        'role_id' => 'role_id',
        'module_id' => 'module_id',
        'permission' => 'permission',
        'route' => 'route',
        'action' => 'action',
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
    /*public function scopeActive($query)
    {
        return $query->where('is_enable', '!=', Constant::RecordType['DELETED']);
    }*/

    /**
     * Set column Name with Actual name.
     * 
     * @param string $request
     * @return array
     */
    public function filterColumns(Request $request, $method = null){
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

        return "role_id";
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
              //  'client_id' => 'required|integer',
                'role_id' => 'required|integer',
                'module_id' => 'required|array',
                'module_id.*' => 'required|integer',
                'permission' => 'required|array'
            ],
            /*'PUT' => [
                'client_id' => 'required|integer',
                'role_id ' => 'required|integer',
                'module_id' => 'required|integer',
                'route' => 'required|alpha|min:3|max:100',
                'action' => 'required|alpha|min:3|max:100'
            ],
            'PATCH' => [
                'id' => 'required|integer',
                'activate' => 'required|numeric|between:0,1'
            ],*/
            'GET_ONE' => [
                'role_id' => 'integer'
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
            // 'client_id.required' => [
            //     "code" => 10101,
            //     "message" => "Please provide client id."
            // ],
            // 'client_id.integer' => [
            //     "code" => 10106,
            //     "message" => "Client id must be an integer."
            // ],
            
            'role_id.required' => [
                "code" => 10101,
                "message" => "Please provide role id."
            ],
            'role_id.integer' => [
                "code" => 10106,
                "message" => "Role id must be an integer."
            ],
            
            'module_id.required' => [
                "code" => 10101,
                "message" => "Please provide module id."
            ],
            'module_id.integer' => [
                "code" => 10106,
                "message" => "Module id must be an integer."
            ],
            
            'permission.required' => [
                "code" => 10101,
                "message" => "Please provide permission."
            ],
            'permission.array' => [
                "string" => [
                    "code" => 10103,
                    "message" => "The permission must be an array."
                ]
            ],
        ];

        $idMessages = [
            // 'client_id.required' => [
            //     "code" => 10107,
            //     "message" => "Please provide client id."
            // ],
            // 'client_id.integer' => [
            //     "code" => 10106,
            //     "message" => "Client id must be an integer."
            // ],
            'role_id.required' => [
                "code" => 10107,
                "message" => "Please provide role id."
            ],
            'role_id.integer' => [
                "code" => 10106,
                "message" => "Role id must be an integer."
            ]
        ];

        /*$statusMessage = [
            'activate.required' => [
                "code" => 90101,
                "message" => "Please provide activate flag."
            ],
            'activate.numeric' => [
                "code" => 90102,
                "message" => "Activate flag must be an integer."
            ],
            'activate.between' => [
                'numeric' => [
                    "code" => 90103,
                    "message" => "The activate flag must be between :min and :max."
                ]
            ]
        ];*/

        $messages = match ($method) {
            'POST' => $commonMessages,
            // 'PUT' => $commonMessages + $idMessages,
            // 'PATCH' => $idMessages + $statusMessage,
            // 'DELETE' => $idMessages,
            'GET_ONE' => $idMessages,
            'GET_ALL' => $messages = []
        };

        return $messages;
    }
}
