<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    // use HasFactory;

    protected $table = 'wf_master';

    public $timestamps  = false;
    public function Module()
    {
        return $this->belongsTo(AuthModule::class, 'doc_type_id')->select('id', 'name');
    }

    public function Module_name()
    {
        return $this->belongsTo(AuthModule::class, 'module_id')->select('id', 'name');
    }

   

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 
       
        'organization_id', 
        'module_id', 
        'doc_type_id', 
        'wf_name', 
        'wf_desc', 
        'wf_from_date', 
        'wf_end_date', 
        'check_validity', 
        'wf_start_on', 
        'wf_level', 
        
        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at'
       
    ];
    
    protected $tableColumnList = [
        'id' => 'id',
        'data_org_id'   => 'organization_id',
        'module_id'     => 'module_id',
        'doc_type_id'   => 'doc_type_id',
        'wf_name'       => 'wf_name',
        'wf_desc'       => 'wf_desc',
        'wf_from_date'  => 'wf_from_date',
        'wf_end_date'   => 'wf_end_date',
        'check_validity'=> 'check_validity',
        'wf_start_on'   => 'wf_start_on',
        'wf_level'      => 'wf_level',
        'is_enable'     => 'is_enable',
        'created_by'    => 'created_by',
        'created_at'    => 'created_at',
        'updated_by'    => 'updated_by',
        'updated_at'    => 'updated_at',
        'deleted_at'    => 'deleted_at',
        'data_user_id'  => 'data_user_id',
        
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
                // 'organization_id'   => 'required|integer',
                // 'module_id'         => 'required|integer',
                // 'doc_type_id'       => 'required|integer',
                // 'wf_name' => [
                //     'required', Rule::unique('wf_master', 'wf_name')->where(function ($query) use ($request) {
                //         $query->where('is_enable', '<>', '2');
                //         $query->where('organization_id', '=', $request->organization_id);
                //     }),
                //     'min:3', 'max:50'
                // ],
                // 'wf_desc'           => 'required|min:3|max:300',
                // 'wf_from_date'      => 'required|date',
                // 'wf_end_date'       => 'required|date',
                // 'check_validity'    => 'required|integer',
                // 'wf_start_on'       => 'required|integer',
                // 'wf_level'          => 'required|integer',
            ],
            'PUT' => [
              
               
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
            
        //     'slip_due_date.required' => [
        //         "code" => 10101,
        //         "message" => "Please provide Due Date."
        //     ],
        //    'slip_valid_date.required' => [
        //         "code" => 10101,
        //         "message" => "Please provide valid Date."
        //     ],
        //    'month.required' => [
        //         "code" => 10101,
        //         "message" => "Please provide Month."
        //     ],

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
