<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class CampusSeatingCapacity extends Model
{
    // use HasFactory;

    protected $table = 'campus_seating_capacity';

    public $timestamps  = false;

    
    // public function Country()
    // {
    //     return $this->belongsTo(Country::class, 'countries_id')->select('id','country_name');
    // }
    // public function State()
    // {
    //     return $this->belongsTo(State::class, 'state_id')->select('id','state_name');
    // }
    // public function Region()
    // {
    //     return $this->belongsTo(Region::class, 'region_id')->select('id','region_name');
    // }
    // public function City()
    // {
    //     return $this->belongsTo(City::class, 'city_id')->select('id','city_name');
    // }
    public function Campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id')->select('id','campus_name');
    }
    public function Class()
    {
        return $this->belongsTo(Classes::class, 'class_id')->select('id','class_name');
    }
    public function Subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id')->select('id','subject_name');
    }
    public function Section()
    {
        return $this->belongsTo(Section::class, 'section_id')->select('id','section_name');
    }
    public function Session()
    {
        return $this->belongsTo(Session::class, 'session_id')->select('id','session_name');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 
        // 'organization_id', 
        // 'countries_id', 
        // 'state_id', 
        // 'region_id',
        // 'city_id',       
        //'session_id', 
        // 'subject_id',
        'new_student_no',
        'campus_id', 
        'class_id',         
        'section_id',
        'gender', 
        'dimension_capacity', 
        'reserved_capacity',
        'fixed_capacity',
        'room_no',
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
        'new_student_no' => 'new_student_no',
        // 'organization_id'       => 'organization_id',
        // 'countries_id'          => 'countries_id',
        // 'state_id'              => 'state_id',
        // 'region_id'             => 'region_id',
        // 'city_id'               => 'city_id',
        // 'subject_id'            => 'subject_id', 
        // 'session_id'            => 'session_id', 
        'campus_id'             => 'campus_id',        
        'class_id'              => 'class_id',         
        'section_id'            => 'section_id', 
        'gender'                => 'gender', 
        'dimension_capacity'    => 'dimension_capacity',
        'reserved_capacity'     => 'reserved_capacity',
        'fixed_capacity'        => 'fixed_capacity',
        'room_no'               => 'room_no',
        'is_enable'             => 'is_enable',
        'created_by'            => 'created_by',
        'created_at'            => 'created_at',
        'updated_by'            => 'updated_by',
        'updated_at'            => 'updated_at',
        'deleted_at'            => 'deleted_at'
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
                // 'countries_id' => 'required|integer',
                // 'state_id' => 'required|integer',
                // 'region_id' => 'required|integer',
                // 'city_id' => 'required|integer',
                // 'session_id' => 'required|integer',
                'campus_id' => 'required|integer',
                'class_id' => 'required|integer',
                'gender' => 'required',
                'fixed_capacity' => 'required|integer',
                'section_id' => [
                    'required', Rule::unique('campus_seating_capacity', 'section_id')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                        $query->where('campus_id', '=', $request->campus_id);
                        $query->where('section_id', '=', $request->section_id);
                        //$query->where('session_id', '=', $request->session_id);
                        $query->where('class_id', '=', $request->class_id);
                        $query->where('gender', '=', $request->gender);
                    }),
                    'integer'
                ],
            ],
            'PUT' => [
                // 'countries_id' => 'required|integer',
                // 'state_id' => 'required|integer',
                // 'region_id' => 'required|integer',
                // 'city_id' => 'required|integer',
                'campus_id' => 'required|integer',
                // 'session_id' => 'required|integer',
                'class_id' => 'required|integer',
                'gender' => 'required',
                'fixed_capacity' => 'required|integer',
                'section_id' => [
                    'required', Rule::unique('campus_seating_capacity', 'section_id')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                        $query->where('campus_id', '=', $request->campus_id);
                        $query->where('class_id', '=', $request->class_id);
                        $query->where('gender', '=', $request->gender);
                        // $query->where('section_id_', '=', $request->section_id);
                        $query->where('id', '<>', $request->id);
                    }),
                    'integer'
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
            'section_id.required' => [
                "code" => 10101,
                "message" => "Please provide Section ."
            ],
            'section_id.unique' => [
                "code" => 10102,
                "message" => "Please provide unique Section."
            ],

            // 'country_id.required' => [
            //     "code" => 10101,
            //     "message" => "Please provide country id."
            // ],
            // 'state_id.required' => [
            //     "code" => 10101,
            //     "message" => "Please provide State id."
            // ],
            // 'region_id.required' => [
            //     "code" => 10101,
            //     "message" => "Please provide region id."
            // ],
            // 'city_id.required' => [
            //     "code" => 10101,
            //     "message" => "Please provide city id."
            // ],
            'campus_id.required' => [
                "code" => 10101,
                "message" => "Please provide Campus id."
            ],
            'session_id.required' => [
                "code" => 10101,
                "message" => "Please provide Session id."
            ],
            'class_id.required' => [
                "code" => 10101,
                "message" => "Please provide Class id."
            ],
            'gender.required' => [
                "code" => 10101,
                "message" => "Please provide Gender"
            ],
            'fixed_capacity.required' => [
                "code" => 10101,
                "message" => "Please provide Fixed Capacity."
            ], 

        ];

        $idMessages = [
            'id.required' => [
                "code" => 10107,
                "message" => "Please provide id."
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
