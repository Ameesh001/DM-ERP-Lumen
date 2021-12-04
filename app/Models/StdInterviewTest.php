<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class StdInterviewTest extends Model
{
    // use HasFactory;

    protected $table = 'std_registration_interview_test';

    public $timestamps  = false;
    public function Class()
    {
        return $this->belongsTo(Classes::class, 'class_id')->select('id','class_name');
    }

    public function ForSection()
    {
        return $this->belongsTo(StudentRegistration::class, 'std_registration_id')
        ->select('id','section_id','class_id','gender');

     
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'registration_code', 
        'std_registration_id',
        'obtained_marks',
        'final_result_id',
        'test_date',
        'test_remarks',
        'is_interview',
        'interview_remarks',
        'interview_date',
        'is_nazra_test',
        'nazra_current_lesson',
        'nazra_date',
        'nazra_remarks' ,
        'is_seat_alloted',
        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at'
    ];

    protected $tableColumnList = [
        'data_user_id' => 'data_user_id',
        'id',
        'registration_code', 
        'std_registration_id',
        'obtained_marks',
        'final_result_id',
        'test_date',
        'test_remarks',
        'is_interview',
        'interview_remarks',
        'interview_date',
        'is_nazra_test',
        'nazra_current_lesson',
        'nazra_date',
        'nazra_remarks' ,
        'is_seat_alloted',
        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at'
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
                'name' => [
                    'required', Rule::unique('countries', 'country_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                    }),
                    'min:3', 'max:100' //'alpha_space', 
                ],
                'full_name' => 'min:3|max:150', //alpha_space
                'dial_code' => 'required|numeric|digits_between:1,10',
                'short_code' => [
                    'required', Rule::unique('countries', 'short_code')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                    }),
                    'alpha', 'min:2', 'max:3'
                ],
            ],
            'PUT' => [
                'id' => 'required|integer',
                'name' => [
                    'required', Rule::unique('countries', 'country_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                        ->where('id', '<>', $request->id);
                    }),
                    'min:3', 'max:100' //'alpha_space', 
                ],
                'full_name' => 'min:3|max:150', //alpha_space
                'dial_code' => 'required|numeric|digits_between:1,10',
                'short_code' => [
                    'required', Rule::unique('countries', 'short_code')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                        ->where('id', '<>', $request->id);
                    }),
                    'alpha', 'min:2', 'max:3'
                ],
                'short_code' => 'required|unique:countries,short_code,' . $request->id.'|alpha|min:2|max:3'
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
            'name.required' => [
                "code" => 10101,
                "message" => "Please provide country name."
            ],
            'name.unique' => [
                "code" => 10102,
                "message" => "Please provide unique country name."
            ],
            'name.alpha_space' => [
                "code" => 10103,
                "message" => "The country name may only contain letters and spaces."
            ],
            'name.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The country name must be at least :min characters."
                ]
            ],
            'name.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The country name may not be greater than :max characters."
                ]
            ],
            'name.alpha_space' => [
                "code" => 10103,
                "message" => "The country name may only contain letters and spaces."
            ],
            
            'full_name.alpha_space' => [
                "code" => 10103,
                "message" => "The country full name may only contain letters and spaces."
            ],
            'full_name.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The country full name must be at least :min characters."
                ]
            ],
            'full_name.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The country full name may not be greater than :max characters."
                ]
            ],

            'dial_code.required' => [
                "code" => 10101,
                "message" => "Please provide country dialing code."
            ],
            'dial_code.numeric' => [
                "code" => 10103,
                "message" => "The country code may only contain numbers."
            ],
            'dial_code.digits_between' => [
                "code" => 10106,
                "message" => "The country code must between 1 and 10 digits."
            ],

            'short_code.required' => [
                "code" => 10101,
                "message" => "Please provide country ISO code."
            ],
            'short_code.alpha' => [
                "code" => 10103,
                "message" => "The country ISO code may only contain letters."
            ],
            'short_code.min' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The country ISO code must be at least :min characters."
                ]
            ],
            'short_code.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The country ISO code may not be greater than :max characters."
                ]
            ],
            'short_code.unique' => [
                "code" => 10102,
                "message" => "Please provide unique country ISO code."
            ],

        ];

        $idMessages = [
            'id.required' => [
                "code" => 10107,
                "message" => "Please provide country id."
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
