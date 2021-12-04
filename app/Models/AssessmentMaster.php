<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

use App\Models\AssessmentType;
class AssessmentMaster extends Model
{
    // use HasFactory;

    protected $table = 'assessment_master';

    public $timestamps  = false;

    public function AssessmentType()
    {
        return $this->belongsTo(AssessmentType::class, 'assessment_type_id', 'id')->select('id', 'assessment_type_name');
    }
    public function AssessmentCategory()
    {
        return $this->belongsTo(AssessmentCategory::class, 'assessment_category_id', 'id')->select('id', 'assessment_category_name');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'assessment_category_id',
        'assessment_type_id',
        'title',
        'assessment_remarks',
        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at'
    ];

    protected $tableColumnList = [
        'id' => 'id',
        'assessment_category_id'    => 'assessment_category_id',
        'assessment_type_id'    => 'assessment_type_id',
        'title'                 => 'title',
        'assessment_remarks'    => 'assessment_remarks',

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
                'assessment_category_id' => [
                    'required'
                ],
                'assessment_type_id' => [
                    'required'
                ],
                'title' => [
                    'required','min:3', 'max:100'
                ],
                'assessment_remarks' => [
                    'required','min:3', 'max:100'
                ],
              
                
            ],
            'PUT' => [
                'id' => 'required|integer',
                'assessment_category_id' => [
                    'required'
                ],
                'assessment_type_id' => [
                    'required'
                ],
                'title' => [
                    'required','min:3', 'max:100'
                ],
                'assessment_remarks' => [
                    'required','min:3', 'max:100'
                ],
               
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
            // 'title.required' => [
            //     "code" => 10101,
            //     "message" => "Please provide Assessment Type Name."
            // ],
            // 'title.unique' => [
            //     "code" => 10102,
            //     "message" => "Please provide unique Assessment Type Name."
            // ],

            // 'title.min' => [
            //     "string" => [
            //         "code" => 10104,
            //         "message" => "The Assessment Type Name must be at least :min characters."
            //     ]
            // ],
            // 'title.max' => [
            //     "string" => [
            //         "code" => 10105,
            //         "message" => "The Assessment Type Name may not be greater than :max characters."
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
