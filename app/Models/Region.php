<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    // use HasFactory;

    protected $table = 'region';

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
        'countries_id',
        'state_id',
        'organization_id',
        'region_name',
        'region_desc',
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
        'countries_id' => 'countries_id',
        'state_id'     => 'state_id',
        'organization_id'     => 'organization_id',
        'data_org_id'     => 'data_org_id',
        'region_name' => 'region_name',
        'region_desc' => 'region_desc',
        'is_enable'   => 'is_enable',
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
                'region_name' => [
                    'required', Rule::unique(Constant::Tables['region'], 'region_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                        $query->where('countries_id', '=', $request->countries_id);
                        $query->where('state_id', '=', $request->state_id);
                    }),
                    'min:3', 'max:25'
                ],
                'countries_id' => 'required',
                'state_id' => 'required',
                'region_desc' => ['required','min:3', 'max:100'],
                
            ],
            'PUT' => [
                'id' => 'required|integer',
                'region_name' => [
                    'required', Rule::unique(Constant::Tables['region'], 'region_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                        ->where('id', '<>', $request->id);
                         $query->where('countries_id', '=', $request->countries_id);
                         $query->where('state_id', '=', $request->state_id);
                    }),
                    'min:3', 'max:25'
                ],
                'countries_id' => 'required',
                'state_id' => 'required',
                'region_desc' => ['required','min:3', 'max:100'],
                
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
            'region_name.required' => [
                "code" => 10101,
                "message" => "Please provide Region Name."
            ],
            'region_name.unique' => [
                "code" => 10102,
                "message" => "Please provide unique Region name."
            ],

            'region_name.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The Region name must be at least :min characters."
                ]
            ],
            'region_name.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The Region name may not be greater than :max characters."
                ]
            ],
            'region_Code.required' => [
                "code" => 10101,
                "message" => "Please provide Region Code."
            ],
            'region_code.unique' => [
                "code" => 10102,
                "message" => "Please provide unique Region Code."
            ],
            'region_desc.required' => [
                "code" => 10101,
                "message" => "Please provide Region Description."
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
