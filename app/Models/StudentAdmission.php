<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class StudentAdmission extends Model
{
    // use HasFactory;

    protected $table = 'student_admission';

    public $timestamps  = false;

    
   
    public function Campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id')->select('id','campus_name', 'organization_id', 'countries_id' ,'state_id' , 'region_id', 'city_id');
    }

    public function Class()
    {
        return $this->belongsTo(Classes::class, 'class_id')->select('id','class_name');
    }

    public function Session()
    {
        return $this->belongsTo(Session::class, 'session_id')->select('id','session_name');
    }
    
    public function Section()
    {
        return $this->belongsTo(Section::class, 'section_id')->select('id','section_name');
    }
    public function Organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id')->select('id','org_name');
    }
    
    
    public function FeeStructureCodeWise()
    {
        return $this->belongsTo(AssignFeeStructure::class, 'organization_id','admission_code')->select('id','fees_code');
    }
    
    public function FeeStructureClassWise()
    {
        return $this->belongsTo(AssignFeeStructure::class, 'organization_id', 'campus_id','class_id')->select('id','fees_code');
    }
    
    public function FeeStructureCampusWise()
    {
        return $this->belongsTo(AssignFeeStructure::class, 'organization_id', 'campus_id')->select('id','fees_code')->whereNull('class_id');
    }


    public function FeeMasterSlip()
    {
        return $this->belongsToMany(GenMonthlyVoucher::class, 'std_admission_id', 'id')->select('*');
    }
    
//    public function FeeStructureCityWise()
//    {
//        return $this->belongsTo(AssignFeeStructure::class, 'organization_id', 'city_id')->select('id','fees_code');
//    }
    
   

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 
        'std_registration_id',
        'registration_code',
        'admission_code',
        'gr_no',
        'session_id', 
        'admission_date', 
        'admission_month', 
        'joinning_date', 
        'batch', 
        'organization_id', 
        'campus_id', 
        'class_id', 
        'section_id', 
        'student_name',
        'father_name',
        'gender',
        'dob',
        'father_nic',
        'mother_nic',
        'home_cell_no',
        'father_cell_no', 
        'mother_cell_no',
        'home_address',
        'place_of_birth',
        'blood_group',
        'religion', 
        'nationality',
        'caste',
        'community',
        'is_physically_fit',
        'school_last_attended',
        'grade',
        'native_language',
        'other_language',
        'student_img',
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
        
        'std_registration_id' => 'std_registration_id',
        'registration_code' => 'registration_code',
        'admission_code' => 'admission_code',
        'gr_no' => 'gr_no',
        'session_id' => 'session_id',
        'admission_date' => 'admission_date',
        'admission_month' => 'admission_month',
        'joinning_date' => 'joinning_date',
        'batch' => 'batch',
        'organization_id' => 'organization_id', 
        'campus_id' => 'campus_id',
        'class_id' => 'class_id',
        'section_id' => 'section_id',
        'student_name' => 'student_name',
        'father_name' => 'father_name',
        'gender' => 'gender',
        'dob' => 'dob',
        'father_nic' => 'father_nic',
        'mother_nic' => 'mother_nic',
        'home_cell_no' => 'home_cell_no',
        'father_cell_no' => 'father_cell_no',
        'mother_cell_no' => 'mother_cell_no',
        'home_address' => 'home_address',
        'place_of_birth' => 'place_of_birth',
        'blood_group' => 'blood_group',
        'religion' => 'religion',
        'nationality' => 'nationality',
        'caste' => 'caste',
        'community' => 'community',
        'is_physically_fit' => 'is_physically_fit',
        'school_last_attended' => 'school_last_attended',
        'grade' => 'grade',
        'native_language' => 'native_language',
        'other_language' => 'other_language',

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
                // 'nationality_id' => 'required|integer',
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
            ],
            'PUT' => [
                'id' => 'required|integer',
                // 'nationality_id' => 'required|integer',
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
                '*.obtained_marks' => 'required|integer',
                
            ],
            'PUT' => [
               '*.obtained_marks' => 'required|integer',
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
            
            // 'nationality_id.required' => [
            //     "code" => 10101,
            //     "message" => "Please provide country id."
            // ],
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
