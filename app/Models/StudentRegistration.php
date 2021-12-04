<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class StudentRegistration extends Model
{
    // use HasFactory;

    protected $table = 'student_registration';

    public $timestamps  = false;

    public function Organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id')->select('id', 'org_logo', 'org_name as org_name');
    }

    public function Country()
    {
        return $this->belongsTo(Country::class, 'nationality_id')->select('id','country_name');
    }
    public function State()
    {
        return $this->belongsTo(State::class, 'state_id')->select('id','state_name');
    }
    public function Region()
    {
        return $this->belongsTo(Region::class, 'region_id')->select('id','region_name');
    }
    public function City()
    {
        return $this->belongsTo(City::class, 'city_id')->select('id','city_name');
    }
   
    public function Campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id')->select('id','campus_name');
    }

    public function Class()
    {
        return $this->belongsTo(Classes::class, 'class_id')->select('id','class_name');
    }
    public function Section()
    {
        return $this->belongsTo(Section::class, 'std_registration_section_id')->select('id','section_name');
    }

    public function Session()
    {
        return $this->belongsTo(Session::class, 'session_id')->select('id','session_name');
    }
    public function reg_slip_master()
    {
        return $this->belongsTo(GenAdmissionVoucher::class, 'std_registration_id')->select('id','slip_month_name');
    }
    
   

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'organization_id', 
        'nationality_id', 
        'campus_id',
        'section_id', 
        'session_id', 
        'class_id', 
        'admission_type_id', 
        'reg_code_prefix', 
        'registration_code', 
        'registration_date', 
        'first_name', 
        'last_name', 
        'full_name', 
        'address', 
        'dob', 
        'religion', 
        'father_name', 
        'father_nic', 
        'email', 
        'phone_no', 
        'father_cell_no', 
        'mother_cell_no', 
        'father_occupation', 
        'prev_school', 
        'reason_for_leaving', 
        'student_age', 
        'gender', 
        'is_required_test', 
        'test_date', 
        'test_time', 
        'student_img', 
        'comments', 
        'is_wf_required', 
        
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
        
        'organization_id'        => 'organization_id',
        'nationality_id'        => 'nationality_id',
       
        'campus_id'             => 'campus_id', 
        'session_id'            => 'session_id', 
        'reg_code_prefix'       => 'reg_code_prefix', 
        'class_id'              => 'class_id', 
        'admission_type_id'     => 'admission_type_id', 
        'registration_code'     => 'registration_code', 
        'registration_date'     => 'registration_date', 
        'first_name'            => 'first_name', 
        'last_name'             => 'last_name', 
        'full_name'             => 'full_name', 
        'address'               => 'address', 
        'dob'                   => 'dob', 
        'religion'              => 'religion', 
        'father_name'           => 'father_name', 
        'father_nic'            => 'father_nic', 
        'email'                 => 'email', 
        'phone_no'              => 'phone_no',        
        'father_cell_no'        => 'father_cell_no',        
        'mother_cell_no'        => 'mother_cell_no',        
        'father_occupation'     => 'father_occupation',        
        'prev_school'           => 'prev_school',        
        'reason_for_leaving'    => 'reason_for_leaving',        
        'student_age'           => 'student_age',        
        'gender'                => 'gender',        
        'is_required_test'      => 'is_required_test',        
        'test_date'             => 'test_date',        
        'test_time'             => 'test_time',        
        'student_img'           => 'student_img',        
        'comments'              => 'comments',        
        'is_wf_required'        => 'is_wf_required',        

        'is_enable'             => 'is_enable',
        'created_by'            => 'created_by',
        'created_at'            => 'created_at',
        'updated_by'            => 'updated_by',
        'updated_at'            => 'updated_at',
        'deleted_at'            => 'deleted_at'
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
     * Get column for ordering after verification.
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
                'nationality_id' => 'required|integer',
                'campus_id' => 'required|integer',
                'session_id' => 'required|integer',
                'class_id' => 'required|integer',
                'registration_date' => 'required',
                'first_name' => ['required','min:3', 'max:100', 'regex:/^[\pL\s\-]+$/u'],
                
                'religion' => 'required|min:3|max:50',
                'dob' => 'required',
                'gender' => 'required',
                'is_required_test' => 'required',
                'father_nic' => ['required', 'numeric'],
                
                'admission_type_id' => 'required|integer',
                'father_name' => ['required','min:3', 'max:100', 'regex:/^[\pL\s\-]+$/u'],
                'father_occupation' => ['required'],
                'phone_no' => ['required', 'numeric'],
                'father_cell_no' => ['required', 'numeric'],

                'first_name' => [
                    'required', Rule::unique(Constant::Tables['student_registration'], 'first_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                        ->where('last_name', '=', $request->last_name)
                        ->where('father_nic', '=', $request->father_nic);
                    }),
                    'min:3', 'max:50'
                ],
                'address' => ['required','min:3', 'max:500', 'regex:/^[\pL\s\-]+$/u'],

            ],
            'PUT' => [
                'id' => 'required|integer',
                'nationality_id' => 'required|integer',
                'campus_id' => 'required|integer',
                'session_id' => 'required|integer',
                'class_id' => 'required|integer',
                'registration_date' => 'required',
                'first_name' => ['required','min:3', 'max:100', 'regex:/^[\pL\s\-]+$/u'],
                
                'religion' => 'required|min:3|max:50',
                'dob' => 'required',
                'gender' => 'required',
                'is_required_test' => 'required',
                'father_nic' => ['required', 'numeric'],
                
                'admission_type_id' => 'required|integer',
                'father_name' => ['required','min:3', 'max:100', 'regex:/^[\pL\s\-]+$/u'],
                'father_occupation' => ['required'],
                'phone_no' => ['required', 'numeric'],
                'father_cell_no' => ['required', 'numeric'],

                'first_name' => [
                    'required', Rule::unique(Constant::Tables['student_registration'], 'first_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                        ->where('last_name', '=', $request->last_name)
                        ->where('id', '<>', $request->id)
                        ->where('father_nic', '=', $request->father_nic);
                    }),
                    'min:3', 'max:50'
                ],
                'address' => ['required','min:3', 'max:500', 'regex:/^[\pL\s\-]+$/u'],
               
            ],
            'PATCH' => [
                'id' => 'required|integer',
                'is_enable' => 'required|numeric|between:0,1'
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
    
    public function testRules($request, $method = null)
    {
        if ($method == null) {
            $method = $request->method();
        }

        $rules = [];

        $rules = match ($method) {
            'POST' => [
                // '*.obtained_marks' => 'required|integer',
                
            ],
            'PUT' => [
            //    '*.obtained_marks' => 'required|integer',
            ],
            'PATCH' => [
                'id' => 'required|integer',
                'is_enable' => 'required|numeric|between:0,1'
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
            
            'nationality_id.required' => [
                "code" => 10101,
                "message" => "Please provide country id."
            ],
           'campus_id.required' => [
                "code" => 10101,
                "message" => "Please provide Campus id."
            ],
            'session_id.required' => [
                "code" => 10101,
                "message" => "Please provide Session id."
            ],
            'class_id.required' => [
                "code" => 10101,
                "message" => "Please provide Class id."
            ],
            'gender.required' => [
                "code" => 10101,
                "message" => "Please provide Gender"
            ],
            'registration_date.required' => [
                "code" => 10101,
                "message" => "Please provide Registration Date"
            ],
            'first_name.required' => [
                "code" => 10101,
                "message" => "Please provide First Name"
            ],
            'religion.required' => [
                "code" => 10101,
                "message" => "Please provide Religion "
            ],
            'dob.required' => [
                "code" => 10101,
                "message" => "Please provide Date of Birth "
            ],
            'is_required_test.required' => [
                "code" => 10101,
                "message" => "Please provide Is Required Checked"
            ],
            'father_nic.required' => [
                "code" => 10101,
                "message" => "Please provide Father NIC"
            ],
            'admission_type_id.required' => [
                "code" => 10101,
                "message" => "Please provide admission type"
            ],
            'father_name.required' => [
                "code" => 10101,
                "message" => "Please provide Father Name"
            ],
            'father_occupation.required' => [
                "code" => 10101,
                "message" => "Please provide Father Occupation"
            ],
            'phone_no.required' => [
                "code" => 10101,
                "message" => "Please provide Phone Number"
            ],
            'father_cell_no.required' => [
                "code" => 10101,
                "message" => "Please provide Father Cell Number"
            ],
            


            

        ];

        $idMessages = [
            'id.required' => [
                "code" => 10107,
                "message" => "Please provide id."
            ],
            'id.integer' => [
                "code" => 10106,
                "message" => "Id must be an integer."
            ]
        ];

        $statusMessage = [
            'is_enable.required' => [
                "code" => 90101,
                "message" => "Please provide activate flag."
            ],
            'is_enable.numeric' => [
                "code" => 90102,
                "message" => "Activate flag must be an integer."
            ],
            'is_enable.between' => [
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
    
    public function testMessages($request, $method = null)
    {
        if ($method == null) {
            $method = $request->method();
        }

        $messages = [];

        $commonMessages = [
            
            'obtained_marks.required' => [
                "code" => 10101,
                "message" => "Please provide obtained_marks."
            ],
           
        ];

        $idMessages = [
            'id.required' => [
                "code" => 10107,
                "message" => "Please provide id."
            ],
            'id.integer' => [
                "code" => 10106,
                "message" => "Id must be an integer."
            ]
        ];

        $statusMessage = [
            'is_enable.required' => [
                "code" => 90101,
                "message" => "Please provide activate flag."
            ],
            'is_enable.numeric' => [
                "code" => 90102,
                "message" => "Activate flag must be an integer."
            ],
            'is_enable.between' => [
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
