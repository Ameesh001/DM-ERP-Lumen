<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    // use HasFactory;

    protected $table = 'campus';

    public $timestamps  = false;

    
    public function Country()
    {
        return $this->belongsTo(Country::class, 'countries_id')->select('id','country_name');
    }
    public function State()
    {
        return $this->belongsTo(State::class, 'state_id')->select('id','state_name');
    }
    public function Region()
    {
        return $this->belongsTo(Region::class, 'region_id')->select('id','region_name');
    }
    public function City()
    {
        return $this->belongsTo(City::class, 'city_id')->select('id','city_name');
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
        'region_id',
        'city_id', 
        'campus_name', 
        'campus_address',
        'campus_email', 
        'campus_cell', 
        'principle_name',
        'principle_cell',
        'principle_email', 
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
        'data_org_id' => 'organization_id',
        'countries_id' => 'countries_id',
        'state_id'     => 'state_id',
        'region_id'    => 'region_id',
        'city_id'      => 'city_id',
        'campus_name' => 'campus_name', 
        'campus_address' => 'campus_address',
        'campus_email' => 'campus_email', 
        'campus_cell' => 'campus_cell', 
        'principle_name' => 'principle_name',
        'principle_cell' => 'principle_cell',
        'principle_email' => 'principle_email', 
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
     * Get column for ordering after verification.
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
                'campus_name' => [
                    'required', Rule::unique(Constant::Tables['campus'], 'campus_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                        $query->where('region_id', '=', $request->region_id);
                    }),
                    'min:3', 'max:100'
                ],
                'countries_id' => 'required',
                'state_id' => 'required',
                'region_id' => 'required',
                'city_id' => 'required',
                'campus_address' => ['required','min:3', 'max:100'],
                'campus_email'   => ['required','email', 'max:100'],
                'campus_cell'    => ['required','numeric', 'digits_between:1,11'],
                'principle_name'    => ['required','min:3', 'max:25'],
                'principle_email'   => ['required','email', 'max:100'],
                'principle_cell'    => ['required','numeric', 'digits_between:1,11']
            ],
            'PUT' => [
                'id' => 'required|integer',
                'campus_name' => [
                    'required', Rule::unique(Constant::Tables['campus'], 'campus_name')->where(function ($query) use ($request) {
                                $query->where('is_enable', '<>', '2')
                                      ->where('region_id', '=', $request->region_id)
                                      ->where('id', '<>', $request->id);
                    }),
                    'min:3', 'max:25'
                ],
                'countries_id' => 'required',
                'state_id' => 'required',
                'region_id' => 'required',
                'city_id' => 'required',
                'campus_address' => ['required','min:3', 'max:100'],
                'campus_email'   => ['required','email', 'max:100'],
                'campus_cell'    => ['required','numeric', 'digits_between:1,11'],
                'principle_name'    => ['required','min:3', 'max:25'],
                'principle_email'   => ['required','email', 'max:100'],
                'principle_cell'    => ['required','numeric', 'digits_between:1,11']
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
            'campus_name.required' => [
                "code" => 10101,
                "message" => "Please provide Campus Name."
            ],
            'campus_name.unique' => [
                "code" => 10102,
                "message" => "Please provide unique Campus name."
            ],

            'campus_name.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The Campus name must be at least :min characters."
                ]
            ],
            'campus_name.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The Campus name may not be greater than :max characters."
                ]
            ],
            'campus_address.required' => [
                "code" => 10101,
                "message" => "Please provide Campus Address."
            ],
            'campus_address.unique' => [
                "code" => 10102,
                "message" => "Please provide unique Campus Address."
            ],
            'principle_name.required' => [
                "code" => 10101,
                "message" => "Please provide Principle Name."
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
