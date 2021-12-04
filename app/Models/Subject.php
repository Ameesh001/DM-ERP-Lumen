<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    // use HasFactory;

    protected $table = 'subject';

    public $timestamps  = false;

    
    public function Country()
    {
        return $this->belongsTo(Country::class, 'countries_id')->select('id','country_name');
    }
    public function State()
    {
        return $this->belongsTo(State::class, 'state_id')->select('id','state_name');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',                
        'organization_id',
        'countries_id',
        'state_id',
        'subject_code',
        'subject_name',
        'subject_desc',
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
        'countries_id' => 'countries_id',
        'state_id' => 'state_id',
        'subject_code' => 'subject_code',
        'subject_name' => 'subject_name',
        'subject_desc' => 'subject_desc',
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
                'subject_name' => [
                    'required', Rule::unique('subject', 'subject_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                        $query->where('countries_id', '=', $request->countries_id);
                        $query->where('state_id', '=', $request->state_id);
                    }),
                    'min:3', 'max:100'
                ],
                'subject_code' => [
                    'required', Rule::unique('subject', 'subject_code')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                        $query->where('countries_id', '=', $request->countries_id);
                        $query->where('state_id', '=', $request->state_id);
                    }),
                    'min:3', 'max:6'
                ],
                'countries_id' => 'required',
                'state_id' => 'required',
                'subject_desc' => 'required',
                
            ],
            'PUT' => [
                'id' => 'required|integer',
                'subject_name' => [
                    'required', Rule::unique('subject', 'subject_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                        ->where('id', '<>', $request->id);
                        $query->where('countries_id', '=', $request->countries_id);
                         $query->where('state_id', '=', $request->state_id);
                    }),
                    'min:3', 'max:100'
                ],
                'subject_code' => [
                    'required', Rule::unique('subject', 'subject_code')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                        ->where('id', '<>', $request->id);
                        $query->where('countries_id', '=', $request->countries_id);
                        $query->where('state_id', '=', $request->state_id);
                    }),
                    'min:3', 'max:6'
                ],
                'countries_id' => 'required',
                'state_id' => 'required',
                
                'subject_desc' => 'required',
                
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
            'subject_name.required' => [
                "code" => 10101,
                "message" => "Please provide Subject Name."
            ],
            'subject_name.unique' => [
                "code" => 10102,
                "message" => "Please provide unique Subject name."
            ],

            'subject_name.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The Subject name must be at least :min characters."
                ]
            ],
            'subject_name.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The Subject name may not be greater than :max characters."
                ]
            ],
            'subject_Code.required' => [
                "code" => 10101,
                "message" => "Please provide Subject Code."
            ],
            'subject_code.unique' => [
                "code" => 10102,
                "message" => "Please provide unique Subject Code."
            ],
            
            'subject_desc.required' => [
                "code" => 10101,
                "message" => "Please provide Subject Description."
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
