<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class GradingExam extends Model
{
    // use HasFactory;

    protected $table = 'grading_exam';

    public $timestamps  = false;

    
    public function GradingType()
    {
        return $this->belongsTo(GradingType::class, 'grading_type_id')->select('id','grading_type_name');
    }
    public function GradingRemarks()
    {
        return $this->belongsTo(GradingRemarks::class, 'grading_remarks_id')->select('id','grading_remarks','short_name');
    }

    public function Organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id')->select('*');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'grading_type_id',
        'grading_remarks_id',
        'organization_id',
        'grade_name',
        'percentage_from',
        'percentage_end',
        'desc',
        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at'
    ];

    protected $tableColumnList = [
        'id' => 'id',
        'grading_type_id'   => 'grading_type_id',
        'grading_remarks_id'=> 'grading_remarks_id',
        'data_org_id'   => 'organization_id',
        'grade_name'        => 'grade_name',
        'percentage_from'   => 'percentage_from',
        'percentage_end'    => 'percentage_end',
        'desc'              => 'desc',
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
                'grade_name' => [
                    'required', Rule::unique('grading_exam', 'grade_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                        $query->where('organization_id', '=', $request->organization_id);
                    }),
                    'min:1', 'max:50'
                ],
                'grading_type_id' => 'required',
                'grading_remarks_id' => 'required',
                'percentage_from' => 'required',
                'percentage_end' => 'required',
                'desc' => ['required','min:3', 'max:100'],
                
            ],
            'PUT' => [
                'id' => 'required|integer',
                'grade_name' => [
                    'required', Rule::unique('grading_exam', 'grade_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                        ->where('id', '<>', $request->id);
                         $query->where('organization_id', '=', $request->organization_id);
                    }),
                    'min:1', 'max:50'
                ],
                'grading_type_id' => 'required',
                'grading_remarks_id' => 'required',
                'percentage_from' => 'required',
                'percentage_end' => 'required',
                'desc' => ['required','min:3', 'max:100'],
                
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
            'grade_name.required' => [
                "code" => 10101,
                "message" => "Please provide Grade Name."
            ],
            'grade_name.unique' => [
                "code" => 10102,
                "message" => "Please provide unique Grade name."
            ],

            'grade_name.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The Grade name must be at least :min characters."
                ]
            ],
            'grade_name.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The Grade name may not be greater than :max characters."
                ]
            ],
            'grading_type_id.required' => [
                "code" => 10101,
                "message" => "Please provide Grading Type ID."
            ],
            'grading_remarks_id.required' => [
                "code" => 10102,
                "message" => "Please provide Grading Remarks ID."
            ],
            'percentage_from.required' => [
                "code" => 10102,
                "message" => "Please provide percentage From."
            ],
            'percentage_end.required' => [
                "code" => 10102,
                "message" => "Please provide percentage END."
            ],
            'desc.required' => [
                "code" => 10101,
                "message" => "Please provide Grade Description."
            ],
            'desc.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The Grade Description must be at least :min characters."
                ]
            ],
            'desc.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The Grade Description may not be greater than :max characters."
                ]
            ],

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
