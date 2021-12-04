<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class BasicInfoField extends Model
{
    protected $table = 'basic_info_fields';

    public $timestamps  = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'label',
        'fieldname',
        'input_type',
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
        'label' => 'label',
        'fieldname' => 'fieldname',
        'input_type' => 'input_type',
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
                'label' => 'required|min:3|max:100|custom_alpha_num_dash_dot_coma',
                'fieldname' => [
                    'required', Rule::unique('basic_info_fields', 'fieldname')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                    }),
                    'min:3', 'max:100', 'alpha'
                ],
                'input_type' => 'numeric|digits_between:1,2',
            ],
            'PUT' => [
                'id' => 'required|integer',
                'label' => 'required|min:3|max:100|custom_alpha_num_dash_dot_coma',
                'fieldname' => [
                    'required', Rule::unique('basic_info_fields', 'fieldname')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                        ->where('id', '<>', $request->id);
                    }),
                    'min:3', 'max:100', 'alpha'
                ],
                'input_type' => $detail_required.'numeric|digits_between:1,2',
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
            'label.required' => [
                "code" => 10101,
                "message" => "Please provide label."
            ],
            'label.custom_alpha_num_dash_dot_coma' => [
                "code" => 10103,
                "message" => "The label only contain alphabates, numerics, dashes, undescores, dots, brackets and spaces."
            ],
            'label.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The label must be at least :min characters."
                ]
            ],
            'label.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The label may not be greater than :max characters."
                ]
            ],
            
            'fieldname.required' => [
                "code" => 10101,
                "message" => "Please provide field name."
            ],
            'fieldname.unique' => [
                "code" => 10102,
                "message" => "Please provide unique field name."
            ],
            'fieldname.alpha' => [
                "code" => 10103,
                "message" => "The field name only contain alphabates."
            ],
            'fieldname.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The field name must be at least :min characters."
                ]
            ],
            'fieldname.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The field name may not be greater than :max characters."
                ]
            ],

            'input_type.required' => [
                "code" => 10101,
                "message" => "Please provide kpi input type."
            ],
            'input_type.numeric' => [
                "code" => 10106,
                "message" => "The kpi input type may only contain numbers."
            ],
            'input_type.digits_between' => [
                "code" => 10106,
                "message" => "The kpi input type must between 1 and 2 digits."
            ]

        ];

        $idMessages = [
            'id.required' => [
                "code" => 10107,
                "message" => "Please provide kpi id."
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
