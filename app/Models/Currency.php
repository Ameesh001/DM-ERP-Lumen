<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    // use HasFactory;

    protected $table = 'currency';

    public $timestamps  = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'currency_name',
        'currency_symbol',
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
        'currency_name' => 'currency_name',
        'currency_symbol' => 'currency_symbol',
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
                'currency_name' => [
                    'required', Rule::unique('currency', 'currency_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                    }),
                    'min:3', 'max:100'
                ],
                'currency_symbol' => [
                    'required', Rule::unique('currency', 'currency_symbol')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                    }),
                    'min:1', 'max:5'
                ],
                
            ],
            'PUT' => [
                'id' => 'required|integer',
                'currency_name' => [
                    'required', Rule::unique('currency', 'currency_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                        ->where('id', '<>', $request->id);
                    }),
                    'min:3', 'max:100'
                ],
                'currency_symbol' => [
                    'required', Rule::unique('currency', 'currency_symbol')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                        ->where('id', '<>', $request->id);
                    }),
                    'min:1', 'max:5'
                ],
                
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
            'currency_name.required' => [
                "code" => 10101,
                "message" => "Please provide Currency name."
            ],
            'currency_name.unique' => [
                "code" => 10102,
                "message" => "Please provide unique Currency name."
            ],
            'currency_symbol.required' => [
                "code" => 10101,
                "message" => "Please provide Symbol name."
            ],
            'currency_symbol.unique' => [
                "code" => 10102,
                "message" => "Please provide unique Symbol name."
            ],
            'name.alpha_space' => [
                "code" => 10103,
                "message" => "The Currency name may only contain letters and spaces."
            ],
            'name.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The Currency name must be at least :min characters."
                ]
            ],
            'name.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The Currency name may not be greater than :max characters."
                ]
            ],
            'name.alpha_space' => [
                "code" => 10103,
                "message" => "The Currency name may only contain letters and spaces."
            ],

        ];

        $idMessages = [
            'id.required' => [
                "code" => 10107,
                "message" => "Please provide Currency id."
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
