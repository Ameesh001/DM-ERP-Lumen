<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class GenMonthlyVoucherHistory extends Model
{
    // use HasFactory;

    protected $table = 'fee_slip_history';

    public $timestamps  = false;

   
    public function FeesType()
    {
        return $this->belongsTo(Feetype::class, 'fees_type_id')->select('id','fee_type');
    }
    public function DiscountType()
    {
        return $this->belongsTo(DiscountType::class, 'disc_type_id')->select('id','discount_type');
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 
        'fee_slip_id',
        'fee_type_id', 
        'fee_amount', 
        'disc_type_id', 
        'discount_percentage', 
        'discount_amount', 
        'is_discount_entry', 
        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at'
       
    ];
    
    protected $tableColumnList = [
        'id'           => 'id',
        'fee_slip_id'  => 'fee_slip_id',
        'fee_type_id' => 'fee_type_id', 
        'fee_amount'   => 'fee_amount', 
        'disc_type_id' => 'disc_type_id', 
        'discount_percentage'   => 'discount_percentage', 
        'discount_amount'       => 'discount_amount', 
        'is_discount_entry'     => 'is_discount_entry', 
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
                
                'searching_type' => 'required',
                'admission_code' => 'required_if:searching_type,1|nullable',
                'class_id'       => 'required_if:searching_type,2|nullable',
                'month'          => 'required|min:6|max:6',
                'slip_type'      => 'required|numeric',
                'due_date'       => 'required',
                'issue_date'     => 'required'
                
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
            
            'searching_type.required' => [
                "code" => 10101,
                "message" => "Please provide searching type."
            ],
           'admission_code.required' => [
                "code" => 10101,
                "message" => "Please provide admission code."
            ],
           'class_id.required' => [
                "code" => 10101,
                "message" => "Please provide class id."
            ],
           'month.required' => [
                "code" => 10101,
                "message" => "Please provide Month."
            ],
           'slip_type.required' => [
                "code" => 10101,
                "message" => "Please provide slip type."
            ],
           'due_date.required' => [
                "code" => 10101,
                "message" => "Please provide due date."
            ],
           'issue_date.required' => [
                "code" => 10101,
                "message" => "Please provide issue date."
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
