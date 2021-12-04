<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class UserRoles extends Model
{
    protected $table = 'auth_roles';

    public $timestamps  = false;

    public function Organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id')->select('id', 'org_logo', 'org_name as org_name');
    }
    
    // public function department()
    // {
    //     return $this->belongsTo(Department::class, 'dept_id')->select('id', 'dept_name');
    // }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'organization_id',
        // 'dept_id',
        'role_name',
        // 'sorting',
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
        'organization_id' => 'organization_id',
        // 'dept_id' => 'dept_id',
        'role' => 'role_name',
        // 'order' => 'sorting',
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
                'organization_id' => 'required|integer|gt:0',
                // 'dept_id' => 'required|integer|gt:0',
                // 'role' => [
                //     'required', Rule::unique('auth_roles', 'role_name')->where(function ($query) use ($request) {
                //         $query->where('is_enable', '<>', '2')->where('client_id', $request->client_id);
                //     }),
                //     'min:3', 'max:100' //'custom_alpha_num_dash_dot',
                // ],
                'role' => [
                    'required', Rule::unique('auth_roles', 'role_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                            ->where('id', '<>', $request->id)
                            ->where('organization_id', $request->organization_id)
                            ;
                    }),
                    'min:3', 'max:100' //'custom_alpha_num_dash_dot',
                ]
            ],
            'PUT' => [
                'id' => 'required|integer',
                // 'client_id' => 'required|integer|gt:0',
                // 'dept_id' => 'required|integer|gt:0',
                'role' => [
                    'required', Rule::unique('auth_roles', 'role_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                            ->where('id', '<>', $request->id)
                            ->where('organization_id', '=', $request->organization_id)
                            ;
                    }),
                    'min:3', 'max:100' //'custom_alpha_num_dash_dot',
                ]
            ],
            'PATCH' => [
                'id' => 'required|integer',
                'activate' => 'required|numeric|between:0,1'
            ],
            'DELETE' => [
                'id' => 'required|integer',
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
            'role.required' => [
                "code" => 10101,
                "message" => "Please provide role name."
            ],
            // 'role.custom_alpha_num_dash_dot' => [
            //     "code" => 10101,
            //     "message" => "The role may only contain alphabates, numerics, dashes, dots, underscores and spaces."
            // ],
            'role.unique' => [
                "code" => 10102,
                "message" => "Please provide unique role."
            ],
            'role.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The role name must be at least :min characters."
                ]
            ],
            'role.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The role name may not be greater than :max characters."
                ]
            ],

            // 'order.required' => [
            //     "code" => 10107,
            //     "message" => "Please provide order."
            // ],
            // 'order.numeric' => [
            //     "code" => 10106,
            //     "message" => "Order must be a number."
            // ],
            // 'order.min' => [
            //     "string" => [
            //         "code" => 10104,
            //         "message" => "The role order must be at least :min characters."
            //     ]
            // ],
            // 'order.max' => [
            //     "string" => [
            //         "code" => 10105,
            //         "message" => "The role order may not be greater than :max characters."
            //     ]
            // ],
            'organization_id.required' => [
                "code" => 10107,
                "message" => "Please provide role Organization id."
            ],
            'organization_id.integer' => [
                "code" => 10106,
                "message" => "Organization id must be an integer."
            ],
            'organization_id.gt' => [
                "code" => 10106,
                "message" => "Organization id must be greater than 0."
            ],
            // 'dept_id.required' => [
            //     "code" => 10107,
            //     "message" => "Please provide role department id."
            // ],
            // 'dept_id.integer' => [
            //     "code" => 10106,
            //     "message" => "Department id must be an integer."
            // ],
            // 'dept_id.gt' => [
            //     "code" => 10106,
            //     "message" => "Department id must be greater than 0."
            // ]

        ];

        $idMessages = [
            'id.required' => [
                "code" => 10107,
                "message" => "Please provide role id."
            ],
            'id.integer' => [
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
