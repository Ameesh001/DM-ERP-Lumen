<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class ExamMarksRegister extends Model
{
    // use HasFactory;
    protected $table = 'exam_marks_register';
    public $timestamps  = false;

    public function ExamSetups()
    {
        return $this->belongsTo(ExamSetups::class, 'exam_setup_id')->select('*');
    }
    public function Student()
    {
        return $this->belongsTo(StudentAdmission::class, 'std_admission_id')->select('id', 'admission_code', 'student_name','father_name');
    }
    public function AssignExamSubject()
    {
        return $this->belongsTo(AssignExamSubject::class, 'assign_exam_subject_id')->select('id', 'max_marks', 'grading_type_id' ,'subject_id');
    }
    public function Subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id')->select('id', 'subject_name');
    }
    public function Class()
    {
        return $this->belongsTo(Classes::class, 'class_id')->select('id', 'class_name');
    }
    public function Section()
    {
        return $this->belongsTo(Section::class, 'section_id')->select('id', 'section_name');
    }
   

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'examination_id',
        'exam_setup_id',
        'organization_id',
        'session_id',
        'subject_id',
        'assign_exam_subject_id',
        'class_id',
        'section_id',
        'grading_exam_id',
        'std_admission_id',
        'admission_code',
        'gr_no',
        'obtain_marks',
        'percentage',
        
        'exam_attendance',
        
        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at'
    ];

    protected $tableColumnList = [
        // 'id' => 'id',
        
        'examination_id'         => 'examination_id',
        'exam_setup_id'         => 'exam_setup_id',
        'data_organization_id'   => 'organization_id',
        'data_session_id'   => 'session_id',
        'subject_id'   => 'subject_id',
        'assign_exam_subject_id'   => 'assign_exam_subject_id',
        'class_id'   => 'class_id',
        'section_id'   => 'section_id',
        'grading_exam_id'   => 'grading_exam_id',
        'std_admission_id'   => 'std_admission_id',
        'admission_code'   => 'admission_code',
        'gr_no'   => 'gr_no',
        'obtain_marks'   => 'obtain_marks',
        'percentage'   => 'percentage',
        'exam_attendance'   => 'exam_attendance',
        

        // 'is_enable'         => 'is_enable',
        // 'created_by'        => 'created_by',
        // 'created_at'        => 'created_at',
        // 'updated_by'        => 'updated_by',
        // 'updated_at'        => 'updated_at',
        // 'deleted_at'        => 'deleted_at'
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
               
            ],
            'PUT' => [
                
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

        $commonMessages = [];

        $idMessages = [
            'id.required' => [
                "code" => 10107,
                "message" => "Please provide Grade id."
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
