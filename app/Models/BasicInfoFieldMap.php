<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class BasicInfoFieldMap extends Model
{
    protected $table = 'basic_info_flds_map';

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id')->select('id', 'client_name as name');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id')->select('id', 'dept_name');
    }
    
    public function org_hierarchy_level()
    {
        return $this->belongsTo(OrgHierarchyLevels::class, 'org_hier_level_id')->select('id', 'org_hier_level as hl_name');
    }
    
    public function basic_info_field()
    {
        return $this->belongsTo(BasicInfoField::class, 'basic_info_field_id')->select('id', 'label', 'fieldname');
    }

    public $timestamps  = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'client_id',
        'dept_id',
        'org_hier_level_id',
        'basic_info_field_id',
        'sorting',
    ];
    
    protected $tableColumnList = [
        'data_user_id' => 'data_user_id',
        'id' => 'id',
        'client_id' => 'client_id',
        'dept_id' => 'dept_id',
        'hl_id' => 'org_hier_level_id',
        'basic_info_field_id' => 'basic_info_field_id',
        'order' => 'sorting',
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
                'client_id' => 'required|integer|gt:0',
                'dept_id' => 'required|integer|gt:0',
                'hl_id' => 'required|integer|gt:0',
                'basic_info_field_id' => [
                    'required', Rule::unique('basic_info_flds_map', 'basic_info_field_id')->where(function ($query) use ($request) {
                        $query->where('client_id', $request->client_id)->where('dept_id', $request->dept_id)->where('org_hier_level_id', $request->hl_id);
                    }),
                    'integer', 'gt:0'
                ],
                'order' => 'required|numeric|gt:0'
            ],
            'PUT' => [
                'id' => 'required|integer',
                'client_id' => 'required|integer|gt:0',
                'dept_id' => 'required|integer|gt:0',
                'hl_id' => 'required|integer|gt:0',
                'basic_info_field_id' => [
                    'required', Rule::unique('basic_info_flds_map', 'basic_info_field_id')->where(function ($query) use ($request) {
                        $query->where('client_id', $request->client_id)->where('dept_id', $request->dept_id)->where('org_hier_level_id', $request->hl_idd)
                        ->where('id', '<>', $request->id);
                    }),
                    'integer', 'gt:0'
                ],
                'order' => 'required|numeric'
            ],
            'GET_ONE' => [
                'id' => 'required|integer'
                // 'fields' => ''
            ],
            'DELETE' => [
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
            'client_id.required' => [
                "code" => 10101,
                "message" => "Please provide client id."
            ],
            'client_id.integer' => [
                "code" => 10106,
                "message" => "Client id must be an integer."
            ],
            'client_id.gt' => [
                "code" => 10106,
                "message" => "Client id must be greater than 0."
            ],
            
            'dept_id.required' => [
                "code" => 10101,
                "message" => "Please provide department id."
            ],
            'dept_id.integer' => [
                "code" => 10106,
                "message" => "Department id must be an integer."
            ],
            'dept_id.gt' => [
                "code" => 10106,
                "message" => "Department id must be greater than 0."
            ],
            
            'hl_id.required' => [
                "code" => 10101,
                "message" => "Please provide org hierarchy level id."
            ],
            'hl_id.integer' => [
                "code" => 10106,
                "message" => "Org hierarchy level id must be an integer."
            ],
            'hl_id.gt' => [
                "code" => 10106,
                "message" => "Org hierarchy level id must be greater than 0."
            ],
            
            'basic_info_field_id.required' => [
                "code" => 10101,
                "message" => "Please provide basic info field id."
            ],
            'basic_info_field_id.integer' => [
                "code" => 10106,
                "message" => "Basic info field id must be an integer."
            ],
            'basic_info_field_id.gt' => [
                "code" => 10106,
                "message" => "Basic info field id must be greater than 0."
            ],
            'basic_info_field_id.unique' => [
                "code" => 10106,
                "message" => "Basic info field is already mapped."
            ],
            

            'order.required' => [
                "code" => 10101,
                "message" => "Please provide order."
            ],
            'order.numeric' => [
                "code" => 10106,
                "message" => "The order may only contain numbers."
            ]

        ];

        $idMessages = [
            'id.required' => [
                "code" => 10107,
                "message" => "Please provide basic info field map id."
            ],
            'id.integer' => [
                "code" => 10106,
                "message" => "Id must be an integer."
            ]
        ];

        $messages = match ($method) {
            'POST' => $commonMessages,
            'PUT' => $commonMessages + $idMessages,
            'DELETE' => $idMessages,
            'GET_ONE' => $idMessages,
            'GET_ALL' => $messages = []
        };

        return $messages;
    }
}
