<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class NewAdmissionPolicy extends Model
{
    // use HasFactory;

    protected $table = 'new_admission_policy';

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
    public function Campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id')->select('id','campus_name');
    }

    public function Class()
    {
        return $this->belongsTo(Classes::class, 'class_id')->select('id', 'class_name');
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
        'campus_id',
        'class_id',
        'dob_from',
        'dob_end',
        'remarks',
       
        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at'
    ];

    protected $tableColumnList = [
        'data_user_id' => 'data_user_id',
        'id'               => 'id',
        'data_org_id'   => 'organization_id',
        'countries_id'   => 'countries_id',
        'state_id'   => 'state_id',
        'region_id'   => 'region_id',
        'city_id'   => 'city_id',
        'campus_id'   => 'campus_id',
        'class_id'   => 'class_id',
        'dob_from'   => 'dob_from',
        'dob_end'   => 'dob_end',
        'remarks'   => 'remarks',
      
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
                'class_id' => [
                    'required', Rule::unique($this->table, 'class_id')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                        $query->where('organization_id', '=', $request->organization_id);
                        $query->where('countries_id', '=', $request->countries_id);
                        $query->where('state_id', '=', $request->state_id);
                        $query->where('region_id', '=', $request->region_id);
                        $query->where('city_id', '=', $request->city_id);
                        $query->where('campus_id', '=', $request->campus_id);
                        $query->where('dob_from', '=', $request->dob_from);
                        $query->where('dob_end', '=', $request->dob_end);
                    }),
                    'min:1', 'max:10'
                ],
               'dob_from' => 'required|date',
               'dob_end' =>'required|date',
               'remarks' => ['required','min:1', 'max:100'],
               'organization_id' => ['required'],
               'countries_id' => ['required'],
            ],
            'PUT' => [  
                'class_id' => [
                    'required', Rule::unique($this->table, 'class_id')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')->where('id', '<>', $request->id);
                        $query->where('organization_id', '=', $request->organization_id);
                        $query->where('countries_id', '=', $request->countries_id);
                        $query->where('state_id', '=', $request->state_id);
                        $query->where('region_id', '=', $request->region_id);
                        $query->where('city_id', '=', $request->city_id);
                        $query->where('campus_id', '=', $request->campus_id);
                        $query->where('dob_from', '=', $request->dob_from);
                        $query->where('dob_end', '=', $request->dob_end);

                    }),
                    'min:1', 'max:10'
                ],
                'dob_from' => 'required|date',
                'dob_end' =>'required|date',
                'remarks' => ['required','min:1', 'max:100'],
              
               'countries_id' => ['required'],
                
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
            'class_id.required' => [
                "code" => 10101,
                "message" => "Please provide Class."
            ],
            'class_id.unique' => [
                "code" => 10102,
                "message" => "Please provide unique Class. "
            ],
            'min_year.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The Year must be at least :min ."
                ]
            ],
            'max_year.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The Year not be greater than :max characters."
                ]
            ],
            'min_month.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The Month must be at least :min ."
                ]
            ],
            'max_month.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The Month not be greater than :max characters."
                ]
            ],
            
            

        ];

        $idMessages = [
            'id.required' => [
                "code" => 10107,
                "message" => "Please provide Admission Policy id."
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
