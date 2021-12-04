<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class DiscountType extends Model
{
    // use HasFactory;

    protected $table = 'discount_type';

    public $timestamps  = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'discount_type',
        'discount_desc',
        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'organization_id',
        'deleted_at'
    ];

    protected $tableColumnList = [
        'data_user_id' => 'data_user_id',
        'id'               => 'id',
        'data_org_id'   => 'organization_id',
        'discount_type'   => 'discount_type',
        'discount_desc'   => 'discount_desc',
        'activate'         => 'is_enable',
        'created_by'       => 'created_by',
        'created_at'       => 'created_at',
        'updated_by'       => 'updated_by',
        'updated_at'       => 'updated_at',
        'deleted_at'       => 'deleted_at'
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
                'discount_type' => [
                    'required', Rule::unique('discount_type', 'discount_type')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                    }),
                    'min:3', 'max:100', 'regex:/^[\pL\s\-()]+$/u'
                ],
               'discount_desc' => ['required','min:3', 'max:100'],
            ],
            'PUT' => [  
                'id' => 'required|integer',
                'discount_type' => [
                    'required', Rule::unique('discount_type', 'discount_type')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2') ->where('id', '<>', $request->id);
                    }),
                    'min:3', 'max:100','regex:/\([^()]+\)/',
                ],
               'discount_desc' => ['required','min:3', 'max:100'],
                
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
            'discount_type.required' => [
                "code" => 10101,
                "message" => "Please provide Discount Type ."
            ],
            'discount_type.unique' => [
                "code" => 10102,
                "message" => "Please provide unique Discount Type. "
            ],
            'discount_type.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The Discount Type name must be at least :min characters."
                ]
            ],
            'discount_type.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The Discount Type may not be greater than :max characters."
                ]
            ],
            'discount_desc.required' => [
                "code" => 10101,
                "message" => "Please provide Discount Description."
            ],
            'discount_desc.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The Discount Description must be at least :min characters."
                ]
            ],
            'discount_desc.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The Discount Description may not be greater than :max characters."
                ]
            ],
           

        ];

        $idMessages = [
            'id.required' => [
                "code" => 10107,
                "message" => "Please provide Discount Type id."
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
