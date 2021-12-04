<?php
namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class AuthUser extends Model
{
    // use HasFactory;

    protected $table = 'users';

    public $timestamps  = false;

 

    public function user_type()
    {
        return $this->belongsTo(AuthUserType::class, 'user_type')->select('id', 'user_type as name');
    }
    
    public function user_roles_permission()
    {
        return $this->hasMany(AuthUserPerms::class, 'user_id', 'id')->select('id', 'role_id as role');
    }

    public function user_role_level_permission()
    {
        return $this->hasMany(AuthUserPerms::class, 'user_id', 'id')->join('auth_roles', 'user_role_levels.role_id', '=', 'auth_roles.id')->select('user_role_levels.id', 'user_role_levels.role_id as role', 'auth_roles.role_name');
    }

    public function user_data_permission()
    {
        return $this->hasMany(UserDataPermission::class, 'user_id', 'id')->select('id as ud_id', 'hierarchy_level_id' ,'data_permissions_id');
    }

   
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'keycloak_id',
        'role_id',
        'username',
        'full_name',
        'firstName',
        'lastName',
        'phone',
        'email',
        'address',
        'user_type',
        'department_id', 
        'designation_id', 
        'is_teacher',
        'whatsapp_num',
        'gender', 
        'education',
        'reporting_manager',
        'is_manager',
        'is_data_perm',
//        'password',
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
        'keycloak_id' => 'key_cloak_id',
        'role_id' => 'role_id',
        'username' => 'username',
        'firstName' => 'firstName',
        'lastName' => 'lastName',
        'full_name' => 'full_name',
        'phone' => 'phone',
        'email' => 'email',
        'firstName' => 'firstName',
        'lastName' => 'LastName',
        'address' => 'address',
        'user_type' => 'user_type',
        'department_id' => 'department_id', 
        'designation_id' => 'designation_id', 
        'is_teacher' => 'is_teacher',
        'whatsapp_num' => 'whatsapp_num',
        'gender' => 'gender', 
        'education' => 'education',
        'reporting_manager' => 'reporting_manager',
        'is_manager' => 'is_manager',
        'is_data_perm' => 'is_data_perm',
        'password' => 'password',
        'is_enable' => 'activate',
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
                return 'users.'.$key;
        }

        return "users."."id";
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
                'keycloak_id' => 'required',
                'user_type' => 'required|integer',
                'username' => [
                    'required', Rule::unique('users', 'username')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                    }),
                    'min:3', 'max:100', 'custom_alpha_num_dash_dot_nosp',

                ],
                'firstName' => 'required|min:3|max:100', //custom_alpha_dash_dot
                'lastName' => 'required|min:3|max:100', //custom_alpha_dash_dot
                'email' => [
                    'required', Rule::unique('users', 'email')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                    }),
                    'email', 'min:3', 'max:100'
                ],
                'phone' => 'required|numeric|digits_between:11,15',
                'address' => 'custom_alpha_num_dash_dot_coma|min:3|max:100',
                'education' => 'custom_alpha_num_dash_dot_coma|min:3|max:100',
                'is_teacher' => 'required|integer',
                'whatsapp_num' => 'required|numeric|digits_between:11,15',            
                'gender' => 'required|integer',            
                'is_manager' => 'required|integer',            
                //'reporting_manager' => 'integer',            
                            
                
            ],
            'PUT' => [
                'id' => 'required|integer',
                'user_type' => 'required|integer',
                'username' => [
                    'required', Rule::unique('users', 'username')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                            ->where('id', '<>', $request->id);
                    }),
                    'min:3', 'max:100','alpha_num' //'custom_alpha_num_dash_dot_nosp',
                ],
                'email' => [
                    'required', Rule::unique('users', 'email')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                        ->where('id', '<>', $request->id);
                    }),
                    'email', 'min:3', 'max:100'
                ],
                'firstName' => 'required|min:3|max:100', //custom_alpha_dash_dot
                'lastName' => 'required|min:3|max:100', //custom_alpha_dash_dot            
                'phone' => 'required|numeric|digits_between:11,15',
                'address' => 'custom_alpha_num_dash_dot_coma|min:3|max:100',
                'education' => 'custom_alpha_num_dash_dot_coma|min:3|max:100',
                'is_teacher' => 'required|integer',
                'whatsapp_num' => 'required|numeric|digits_between:11,15',            
                'gender' => 'required|integer',            
                'is_manager' => 'required|integer',            
                //'reporting_manager' => 'integer',
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
            'keycloak_id.required' => [
                "code" => 10101,
                "message" => "Please provide keycloak id."
            ],

         
            'user_type.required' => [
                "code" => 10107,
                "message" => "Please provide user type."
            ],
            'user_type.integer' => [
                "code" => 10106,
                "message" => "User type must be an integer."
            ],

            'username.required' => [
                "code" => 10101,
                "message" => "Please provide username."
            ],
            'username.alpha_num' => [
                "code" => 10101,
                "message" => "Only Charactor and Number in username."
            ],
            'username.unique' => [
                "code" => 10102,
                "message" => "Please provide unique username."
            ],
            'username.custom_alpha_num_dash_dot_nosp' => [
                "code" => 10103,
                "message" => "The username may only contain alphabates, numbers, dots, dashes and underscores."
            ],
            'username.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The username must be at least :min characters."
                ]
            ],
            'username.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The username may not be greater than :max characters."
                ]
            ],

            'full_name.required' => [
                "code" => 10101,
                "message" => "Please provide full name."
            ],
            'full_name.custom_alpha_dash_dot' => [
                "code" => 10103,
                "message" => "The full name may only contain alphabates, dots, dashes underscores and spaces."
            ],
            'full_name.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The full name must be at least :min characters."
                ]
            ],
            'full_name.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The full name may not be greater than :max characters."
                ]
            ],

            'email.required' => [
                "code" => 10101,
                "message" => "Please provide email."
            ],
            'email.unique' => [
                "code" => 10102,
                "message" => "Please provide unique email."
            ],
            'email.email' => [
                "code" => 10103,
                "message" => "Please provide valid email."
            ],
            'email.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The email must be at least :min characters."
                ]
            ],
            'email.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The email may not be greater than :max characters."
                ]
            ],

            'phone.required' => [
                "code" => 10101,
                "message" => "Please provide phone number."
            ],
            'phone.numeric' => [
                "code" => 10103,
                "message" => "The phone number may only contain numbers."
            ],
            'phone.digits_between' => [
                "code" => 10106,
                "message" => "The phone number must between 8 or 15 digits."
            ],

            'whatsapp_num.required' => [
                "code" => 1000,
                "message" => "Please provide Whatsapp number number."
            ],
            'whatsapp_num.numeric' => [
                "code" => 1000,
                "message" => "The Whatsapp number may only contain numbers."
            ],
            'whatsapp_num.digits_between' => [
                "code" => 10103,
                "message" => "The Whatsapp number must between 8 or 15 digits."
            ],

            'address.custom_alpha_num_dash_dot_coma' => [
                "code" => 10103,
                "message" => "The address may only contain alphabates, numerics, dashes, undescores, dots, brackets and spaces."
            ],
            'address.min' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The address must be at least :min characters."
                ]
            ],
            'address.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The address may not be greater than :max characters."
                ]
            ],
            'education.required' => [
                "string" => [
                    "code" => 10105,
                    "message" => "Please provide Education."
                ]
            ],
            'education.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The Education must be at least :min characters."
                ]
            ],

            
            'firstName.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The First name must be at least :min characters."
                ]
            ],

            'lastName.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The LastName must be at least :min characters."
                ]
            ],

            /*'user_privileges.required' => [
                "code" => 10101,
                "message" => "Please provide user privileges."
            ],
            'user_privileges.array' => [
                "code" => 10103,
                "message" => "The user privileges must be in array."
            ],
            'user_privileges.*.roles_perms.required' => [
                "code" => 10101,
                "message" => "Please provide user privileges role permission."
            ],
            'user_privileges.*.roles_perms.integer' => [
                "code" => 10101,
                "message" => "The user privileges role permission must be an integer."
            ],
            'user_privileges.*.roles_perms.distinct' => [
                "code" => 10101,
                "message" => "The user privileges role permission has a dublicate value."
            ],
            'user_privileges.*.data_perms.required' => [
                "code" => 10101,
                "message" => "Please provide user privileges data permission."
            ],
            'user_privileges.*.data_perms.integer' => [
                "code" => 10101,
                "message" => "The user privileges data permission must be an integer."
            ],
            'user_privileges.*.data_perms.distinct' => [
                "code" => 10101,
                "message" => "The user privileges data permission has a dublicate value."
            ]*/
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
