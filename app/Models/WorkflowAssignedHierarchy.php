<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class WorkflowAssignedHierarchy extends Model
{
    // use HasFactory;

    protected $table = 'wf_assigned_hierarchy';

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



    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 
        'wf_id', 
        'countries_id', 
        'state_id', 
        'region_id', 
        'city_id', 
        'campus_id', 
        
        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at'
       
    ];
    
    protected $tableColumnList = [
        'id'            => 'id',
        'countries_id'  => 'countries_id',
        'state_id'      => 'state_id',
        'region_id'     => 'region_id',
        'city_id'       => 'city_id',
        'campus_id'     => 'campus_id',
        'is_enable'     => 'is_enable',
        'created_by'    => 'created_by',
        'created_at'    => 'created_at',
        'updated_by'    => 'updated_by',
        'updated_at'    => 'updated_at',
        'deleted_at'    => 'deleted_at', 
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
               'countries_id'   => 'required|integer',
               'wf_id'   => 'required|integer',
            ],
            'PUT' => [
                'countries_id'   => 'required|integer',
                'wf_id'   => 'required|integer',
               
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
            
            'countries_id.required' => [
                "code" => 10101,
                "message" => "Please provide Countries ID ."
            ],
            'wf_id.required' => [
                "code" => 10101,
                "message" => "Please provide WF ID."
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
