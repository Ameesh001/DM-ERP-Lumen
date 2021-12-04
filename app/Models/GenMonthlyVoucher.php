<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class GenMonthlyVoucher extends Model
{
    // use HasFactory;

    protected $table = 'fee_slip_master';

    public $timestamps  = false;

    
    public function Organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id')->select('id','org_logo','org_name');
    }
    
    public function Campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id')->select('id','campus_name');
    }
    
    public function Class()
    {
        return $this->belongsTo(Classes::class, 'class_id')->select('id','class_name');
    }
    
    public function Bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id')->select('id','name','ac_no');
    }
    
    public function FeeSlipDetails()
    {
        return $this->hasMany(GenMonthlyVoucherDetail::class, 'fee_slip_id', 'id')
                            ->leftJoin('fee_type',      'fee_type.id',      '=', 'fee_slip_detail.fee_type_id')
                            ->leftJoin('discount_type', 'discount_type.id', '=', 'fee_slip_detail.disc_type_id')
                            ->select('fee_slip_detail.id', 
                                    'fee_slip_detail.fee_slip_id',
                                    'fee_slip_detail.fee_type_id', 
                                    'fee_slip_detail.fee_amount', 
                                    'fee_slip_detail.disc_type_id', 
                                    'fee_slip_detail.discount_percentage', 
                                    'fee_slip_detail.discount_amount', 
                                    'fee_slip_detail.is_discount_entry', 
                                    'fee_type',
                                    'discount_type',
                                    );
    }
    
//    public function FeeAmountSum()
//    {
//        return $this->hasMany(GenMonthlyVoucherDetail::class, 'fee_slip_id', 'id')
//                              ->selectRaw('SUM(fee_amounts) as fee_amount_total');
//        
//    }
    
    public function stdAdmission()
    {
        return $this->belongsTo(StudentAdmission::class, 'std_admission_id')->select('id', 'student_name', 'father_name', 'gender');
    }
    
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 
       
        'std_admission_id', 
        'fees_master_id', 
        'challan_no', 
        'organization_id', 
        'campus_id', 
        'session_id', 
        'class_id', 
        'section_id',
        'fee_month',
        'fee_month_code',
        'fee_date',
        'fee_actual_date',
        'admission_code', 
        'gr_no', 
        'slip_issue_date', 
        'slip_validity_date', 
        'slip_due_date', 
        'slip_type_id', 
        'kuickpay_id', 
        'bank_id', 
        'payment_by_channel', 

        'total_fees', 
        'total_discount', 
        'total_payable_amount', 
        'total_payable_amount_words', 
        'is_challan_customize', 
        'customize_by_challan_no', 

        'payment_status', 
        'transaction_no', 
      
       
        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at'
       
    ];
    
    protected $tableColumnList = [
        'id' => 'id',
        
        'std_admission_id' => 'std_admission_id' , 
        'fees_master_id'  => 'fees_master_id' , 
        'challan_no'      => 'challan_no' ,
        'organization_id' => 'organization_id' , 
        'campus_id'       => 'campus_id' , 
        'session_id'      => 'session_id' ,
        'class_id'        => 'class_id' , 
        'section_id'      => 'section_id' ,
        'admission_code'  => 'admission_code' ,
        'gr_no'           => 'gr_no' , 
        'slip_issue_date' => 'slip_issue_date' , 
        'slip_validity_date'  => 'slip_validity_date' , 
        'slip_due_date'   => 'slip_due_date' ,
        'slip_type'       => 'slip_type_id' ,
        'kuickpay_id'     => 'kuickpay_id' ,
        'bank_id'         => 'bank_id' ,
        'bank'            => 'bank_id' ,
        
        'fee_month'       => 'fee_month' ,
        'fee_month_code'  => 'fee_month_code' ,
        'fee_date'        => 'fee_date' ,
        'fee_actual_date'        => 'fee_actual_date' ,
        
        'total_fees'      => 'total_fees', 
        'total_discount'  => 'total_discount', 
        'total_payable_amount'       => 'total_payable_amount', 
        'total_payable_amount_words' => 'total_payable_amount_words', 
        
        'is_challan_customize'    => 'is_challan_customize', 
        'customize_by_challan_no' => 'customize_by_challan_no', 
        
        'payment_status' => 'payment_status', 
        'transaction_no' => 'transaction_no',
        'transaction_amount' => 'transaction_amount',
        'transaction_remarks' => 'transaction_remarks',
        'pay_date' => 'pay_date',
        
        'is_enable'             => 'is_enable',
        'created_by'            => 'created_by',
        'created_at'            => 'created_at',
        'updated_by'            => 'updated_by',
        'updated_at'            => 'updated_at',
        'deleted_at'            => 'deleted_at',
        'data_user_id'          => 'data_user_id',
        'data_campus_id'        => 'data_campus_id',
        'data_organization_id'  => 'data_organization_id',
        'searching_type'        => 'searching_type',
        'month'                 => 'month',
        'issue_date'            => 'issue_date',
        'due_date'              => 'due_date',
        'validity_date'         => 'validity_date',
        
        
        
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
    
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function postingRules($request, $method = null)
    {
        if ($method == null) {
            $method = $request->method();
        }

        $rules = [];

        $rules = match ($method) {
            'POST' => [
                
                'transaction_remarks' => 'required',
                'transaction_no'      => 'nullable',
                'transaction_amount'  => 'nullable',
                'bank'                => 'required',
                'admission_code'      => 'required',
                'pay_date'            => 'required',
                
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
    public function postingMessages($request, $method = null)
    {
        if ($method == null) {
            $method = $request->method();
        }

        $messages = [];

        $commonMessages = [
            
            'transaction_remarks.required' => [
                "code" => 10101,
                "message" => "Please provide transaction remarks."
            ],
           'admission_code.required' => [
                "code" => 10101,
                "message" => "Please provide admission code."
            ],
           'transaction_no.required' => [
                "code" => 10101,
                "message" => "Please provide transaction no."
            ],
           'bank.required' => [
                "code" => 10101,
                "message" => "Please provide Bank."
            ],
           'pay_date.required' => [
                "code" => 10101,
                "message" => "Please provide Pay Date."
            ]

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
    
    
     public function advanceRules($request, $method = null)
    {
        if ($method == null) {
            $method = $request->method();
        }

        $rules = [];

        $rules = match ($method) {
            'POST' => [
                
                'admission_code' => 'required',
                'month'          => 'required',
                'slip_type'      => 'required|numeric',
//                'due_date'       => 'required',
//                'issue_date'     => 'required'
                
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
    public function advanceMessages($request, $method = null)
    {
        if ($method == null) {
            $method = $request->method();
        }

        $messages = [];

        $commonMessages = [
            
           'slip_type.required' => [
                "code" => 10101,
                "message" => "Please provide slip_type."
            ],
            
           'admission_code.required' => [
                "code" => 10101,
                "message" => "Please provide admission code."
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
