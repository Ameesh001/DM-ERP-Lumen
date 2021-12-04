<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class AuthModule extends Model
{

    protected $table = 'auth_modules';

    public $timestamps  = false;

    public function modulePermissions()
    {
        return $this->belongsToMany(RolePermission::class, 'auth_role_module_perms');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'default_url',
        // 'module_name',
        'icon_class',
        'parent_id',
        'have_child',
        'allowed_permissions',
        'sorting',
        // 'is_visible',
        'detail',
        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at'
    ];

    protected $tableColumnList = [
        'data_user_id' => 'data_user_id',
        'id' => 'id',
        'ms_name' => 'name',
        'route' => 'default_url',
        'module_name' => 'module_name',
        'icon_class' => 'icon_class',
        'parent_module_id' => 'parent_id',
        'have_child' => 'have_child',
        'permissions' => 'allowed_permissions',
        'sorting' => 'sorting',
        'visible' => 'is_visible',
        'detail' => 'detail',
        'activate' => 'is_enable',
        'created_by' => 'created_by',
        'created_at' => 'created_at',
        'updated_by' => 'updated_by',
        'updated_at' => 'updated_at',
        'deleted_at' => 'deleted_at'
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
    public function filterColumns(Request $request, $cloumn = 1, $method = null)
    {
        if ($method == null) {
            $method = $request->method();
        }
        
        if($request->detail){
            $request->merge([ 'have_child' => 0 ]);
        }else{
            $request->merge( [ 'permissions' => NULL, 'have_child' => 1 ] );
        }
        $columnList = $this->tableColumnList;

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

        foreach ($this->fillable as $key => $value) {

            if ($value === $field)
                return $value;
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
        $allowed_permission_vld = '';
        $parent_id_vld = '';

        if ($method == null) {
            $method = $request->method();
        }

        if ($request->detail) {
            $allowed_permission_vld = 'required';
            $parent_id_vld = '|gt:0';
        }

        $rules = [];
        $rules = match ($method) {
            'POST' => [
                'ms_name' => [
                    'required', Rule::unique('auth_modules', 'name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')->where('parent_id', '=', $request->parent_module_id);
                    }),
                    'min:3', 'max:100' //'custom_alpha_num_dash_dot_coma',
                ],
                'route' => [
                    'required', Rule::unique('auth_modules', 'default_url')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                    }),
                    'valid_route', 'min:3', 'max:100'
                ],
                'parent_module_id' => 'required|integer'.$parent_id_vld,
                'have_child' => 'between:0,1',
                'sorting' => 'required|numeric',
                'is_visible' => 'between:0,1',
                'detail' => 'required|between:0,1',
                'permissions' => $allowed_permission_vld
            ],
            'PUT' => [
                'id' => 'required|integer',
                'ms_name' => [
                    'required', Rule::unique('auth_modules', 'name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')->where('parent_id', '=', $request->parent_module_id)
                        ->where('id', '<>', $request->id);
                    }),
                    'min:3', 'max:100' //'custom_alpha_num_dash_dot_coma',
                ],
                'route' => [
                    'required',Rule::unique('auth_modules', 'default_url')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                        ->where('id', '<>', $request->id);
                    }),
                    'valid_route', 'min:3', 'max:100'
                ],
                'parent_module_id' => 'required|integer'.$parent_id_vld,
                'have_child' => 'between:0,1',
                'sorting' => 'required|numeric',
                'is_visible' => 'between:0,1',
                'detail' => 'required|between:0,1',
                'permissions' => $allowed_permission_vld
            ],
            'PATCH' => [
                'id' => 'required|integer',
                'activate' => 'required|numeric|between:0,1'
            ],
            'DELETE' => [
                'id' => 'required|integer',
            ],
            'GET_ONE' => ['id' => 'required|integer'],
            'GET_ALL' => []
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
            'ms_name.required' => [
                "code" => 10101,
                "message" => "Please provide module name."
            ],
            'ms_name.unique' => [
                "code" => 10102,
                "message" => "Please provide unique module name."
            ],
            'ms_name.valid_route' => [
                "code" => 10105,
                "message" => "The module name may only contain alphabates, numerics, dashes, undescores, dots, brackets and spaces."
            ],
            'ms_name.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The module name must be at least :min characters."
                ]
            ],
            'ms_name.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The module name may not be greater than :max characters."
                ]
            ],

            'parent_module_id.required' => [
                "code" => 10101,
                "message" => "Please provide parent module."
            ],
            'parent_module_id.min' => [
                'numeric' => [
                    "code" => 10104,
                    "message" => "The route must be at least :min."
                ]
            ],
            
            'route.required' => [
                "code" => 10101,
                "message" => "Please provide route."
            ],
            'route.unique' => [
                "code" => 10102,
                "message" => "Please provide unique route."
            ],
            'route.custom_alpha_dash_dot_nosp' => [
                "code" => 10102,
                "message" => "The route may only contain alphabates, dots, dashes, and underscores."
            ],
            'route.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The route must be at least :min characters."
                ]
            ],
            'route.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The route may not be greater than :max characters."
                ]
            ],

            'sorting.required' => [
                "code" => 10101,
                "message" => "Please provide sorting."
            ],

            'sorting.numeric' => [
                "code" => 10101,
                "message" => "Sorting must be a number"
            ],

            'detail.required' => [
                "code" => 10101,
                "message" => "Please provide detail flag."
            ],
            'detail.between' => [
                'numeric' => [
                    "code" => 90103,
                    "message" => "The detail flag must be between :min and :max."
                ]
            ],

            'permissions.required' => [
                "code" => 10101,
                "message" => "Please provide permissions in detail menu."
            ],
        ];

        $idMessages = [
            'lang_code.required' => [
                "code" => 10107,
                "message" => "Please provide module id."
            ],
            'lang_code.integer' => [
                "code" => 10106,
                "message" => "Id must be an integer."
            ]
        ];

        $statusMessage = [
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
        ];

        $messages = match ($method) {
            'POST' => $commonMessages,
            'PUT' => $commonMessages + $idMessages,
            'PATCH' => $idMessages + $statusMessage,
            'DELETE' => $idMessages,
            'GET_ONE' => $idMessages,
            'GET_ALL' => $messages = []
        };

        return $messages;
    }
}
