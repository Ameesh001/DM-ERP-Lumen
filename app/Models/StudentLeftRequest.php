<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class StudentLeftRequest extends Model
{
    // use HasFactory;

    protected $table = 'student_left_request';

    public $timestamps  = false;

    
    public function Campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id')->select('*');
    }

    public function StduentStatus()
    {
        return $this->belongsTo(StudentStatus::class, 'student_status')->select('*');
    }
   

    public function Classes()
    {
        return $this->belongsTo(Classes::class, 'class_id')->select('id','class_name');
    }

   
    public function Student()
    {
        return $this->belongsTo(StudentAdmission::class, 'std_admission_id')->select('*');
    }

    public function Users()
    {
        return $this->belongsTo(AuthUser::class, 'request_assigned_to')->select('id', 'full_name');
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 
        'organization_id', 
        'campus_id', 
        'class_id', 
        'section_id', 
        'std_admission_id', 
        'request_status', 
        'request_assigned_to', 
        'progress', 
        'conduct', 
        'reason_for_leaving', 
        'slc_issue', 
        'remarks', 
        'remarks_for_slc', 
        'left_date', 
        'application_img', 
        'attendance_img', 

        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at'
       
    ];
    
    protected $tableColumnList = [
        'id'                    => 'id',
        'data_org_id'           => 'organization_id',
        'data_campus_id'        => 'campus_id',
        'class_id'              => 'class_id',
        'section_id'            => 'section_id',
        'std_id'                => 'std_admission_id',
        'request_status'        => 'request_status',
        'request_assigned_to'   => 'request_assigned_to',
        'progress'              => 'progress',
        'conduct'               => 'conduct',
        'reason_for_leaving'    => 'reason_for_leaving',
        'slc_issue'             => 'slc_issue',
        'remarks_for_slc'       => 'remarks_for_slc',
        'left_date'             => 'left_date',
        'application_img'       => 'application_img',
        'attendance_img'        => 'attendance_img',
        'remarks'               => 'remarks',

        'is_enable'             => 'is_enable',
        'created_by'            => 'created_by',
        'created_at'            => 'created_at',
        'updated_by'            => 'updated_by',
        'updated_at'            => 'updated_at',
        'deleted_at'            => 'deleted_at',
        'data_user_id'          => 'data_user_id',
        
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
                
                'campus_id' => 'required',
                'class_id' => 'required',
                'section_id' => 'required',
                'std_admission_id' => 'required',
                
                'progress' => 'required',
                'conduct' => 'required',
                'reason_for_leaving' => 'required',
                'remarks' => 'required',
                'remarks_for_slc' => 'required',
                
                'left_date' => 'required',
                

                
            ],
            'PUT' => [
                'campus_id' => 'required',
                'class_id' => 'required',
                'section_id' => 'required',
                'std_admission_id' => 'required',
               
                'progress' => 'required',
                'conduct' => 'required',
                'reason_for_leaving' => 'required',
                'remarks' => 'required',
                'remarks_for_slc' => 'required',
                
                'left_date' => 'required',
               
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
            
            
            'campus_id.required' => [
                "code" => 10107,
                "message" => "Please provide Campus ID."
            ],
            'class_id.required' => [
                "code" => 10107,
                "message" => "Please provide Class ID."
            ],
            'section_id.required' => [
                "code" => 10107,
                "message" => "Please provide Section ID."
            ],
            'std_admission_id.required' => [
                "code" => 10107,
                "message" => "Please provide Student ID."
            ],
            'reason_for_leaving.required' => [
                "code" => 10107,
                "message" => "Please provide Reason for Leaving."
            ],
           
            
            'progress.required' => [
                "code" => 10107,
                "message" => "Please provide Progress."
            ],
            'conduct.required' => [
                "code" => 10107,
                "message" => "Please provide Conduct."
            ],
           'remarks.required' => [
                "code" => 10107,
                "message" => "Please provide Remarks."
            ],
           'remarks_for_slc.required' => [
                "code" => 10107,
                "message" => "Please provide Remarks to SLC."
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
