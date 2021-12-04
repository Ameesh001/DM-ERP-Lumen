<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class ExamSetups extends Model
{
    // use HasFactory;

    protected $table = 'exam_setup';

    public $timestamps  = false;
    public function ExamSetup()
    {
        return $this->belongsTo(ExamSetup::class, 'examination_id')->select('*');
    }

    public function Class()
    {
        return $this->belongsTo(Classes::class, 'class_id')->select('id', 'class_name');
    }
    public function Session()
    {
        return $this->belongsTo(Session::class, 'session_id')->select('*');
    }
    
    public function AssignExamcampus()
    {
        return $this->belongsTo(AssignExamcampus::class, 'assign_exam_campus_id')->select('*');
    }
   
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'examination_id',
        'assign_exam_campus_id',
        'organization_id',
        'campus_id',
        'class_id',
        'session_id',
        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at'
    ];

    protected $tableColumnList = [
        'id' => 'id',
        'examination_id'    => 'examination_id',
        'exam_type_id'      => 'assign_exam_campus_id',
        'data_org_id'       => 'organization_id',
        'data_campus_id'    => 'campus_id',
        'class_id'          => 'class_id',
        'session_id'        => 'session_id',
        'is_enable'         => 'is_enable',
        'created_by'        => 'created_by',
        'created_at'        => 'created_at',
        'updated_by'        => 'updated_by',
        'updated_at'        => 'updated_at',
        'deleted_at'        => 'deleted_at'
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
                'class_id' => [
                    'required', Rule::unique('exam_setup', 'class_id')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                        $query->where('organization_id', '=', $request->organization_id);
                        $query->where('campus_id', '=', $request->campus_id);
                        $query->where('session_id', '=', $request->session_id);
                        $query->where('assign_exam_campus_id', '=', $request->assign_exam_campus_id);
                    }),
                   
                ],
            //    'desc' => ['required','min:3', 'max:100'],
                
            ],
            'PUT' => [
            //     'id' => 'required|integer',
            //     'exam_type' => [
            //         'required', Rule::unique('exam_type', 'exam_type')->where(function ($query) use ($request) {
            //             $query->where('is_enable', '<>', '2')
            //             ->where('id', '<>', $request->id);
            //              $query->where('organization_id', '=', $request->organization_id);
            //         }),
            //         'min:1', 'max:50'
            //     ],
            //    'desc' => ['required','min:3', 'max:100'],
                
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
            'class_id.required' => [
                "code" => 10101,
                "message" => "Please provide Class."
            ],
            'class_id.unique' => [
                "code" => 10102,
                "message" => "Please provide unique Class."
            ],

            // 'exam_type.min' => [
            //     "string" => [
            //         "code" => 10104,
            //         "message" => "The Exam Type must be at least :min characters."
            //     ]
            // ],
            // 'exam_type.max' => [
            //     "string" => [
            //         "code" => 10105,
            //         "message" => "The Exam Type may not be greater than :max characters."
            //     ]
            // ],
            // 'desc.required' => [
            //     "code" => 10101,
            //     "message" => "Please provide Exam Type Description."
            // ],
            // 'desc.min' => [
            //     "string" => [
            //         "code" => 10104,
            //         "message" => "The Exam Type Description must be at least :min characters."
            //     ]
            // ],
            // 'desc.max' => [
            //     "string" => [
            //         "code" => 10105,
            //         "message" => "The Grade Description may not be greater than :max characters."
            //     ]
            // ],

        ];

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
