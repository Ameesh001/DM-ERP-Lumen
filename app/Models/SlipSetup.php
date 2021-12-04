<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SlipSetup extends Model
{
    // use HasFactory;

    protected $table = 'slip_setup';

    public $timestamps  = false;

    
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
        'slip_type_id',
        'session_month_id',
        'month_index',
        'month_close_date',
        'issue_date',
        'due_date',
        'validity_date',
        'is_enable',
        'created_by',
        'created_at' ,
        'updated_by' ,
        'updated_at',
        'deleted_at' 
    ];

    protected $tableColumnList = [

        'id' => 'id',
        'slip_type_id' => 'slip_type_id',
        'session_month_id'=>'session_month_id',
        'month_index'=>'month_index',
        'month_close_date'=>'month_close_date',
        'issue_date' => 'issue_date',
        'due_date'=>'due_date',
        'validity_date'=>'validity_date',
        'is_enable'=>'is_enable',
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


            'POST' =>  [

                'slip_type_id' => 'required',
                'session_month_id' => 'required',
                'month_index' => 'required',                
                'month_close_date' => 'required',                
                'issue_date' => 'required',               
                'due_date' => 'required',                
                'validity_date' => 'required',
                
            ],





            'PUT' =>  [

                'slip_type_id' => 'required',
                'session_month_id' => 'required',
                'month_index' => 'required',                
                'month_close_date' => 'required',                
                'issue_date' => 'required',               
                'due_date' => 'required',                
                'validity_date' => 'required',                
                
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
             'subject_id.required' => [
                 "code" => 10101,
                 "message" => "Please provide Subject Name."
             ],
             'subject_id.unique' => [
                 "code" => 10102,
                 "message" => "Please provide unique Details."
             ],

            'class_id.unique' => [
                "code" => 10104,
                "message" => "Please provide unique Class Start Time"
            ],

            'day.unique' => [
               "code" => 10104,
               "message" => "Please provide unique Class End Time"
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
