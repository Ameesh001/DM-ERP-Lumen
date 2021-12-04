<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\FeeStructureMaster;

class FeeStructureDetail extends Model
{
    // use HasFactory;

    protected $table = 'fee_structure_detail';

    public $timestamps  = false;

    public function Class()
    {
        return $this->belongsTo(Classes::class, 'class_id')->select('id','class_name');
    }

    public function FeesType()
    {
        return $this->belongsTo(Feetype::class, 'fees_type_id')->select('id','fee_type');
    }

    public function FeesMaster()
    {
        return $this->belongsTo(FeeStructureMaster::class, 'fees_master_id');
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 
        'fees_code', 
        'fees_master_id', 
        'fees_type_id', 
        'fees_amount', 
        'fees_from_date', 
        'fees_end_date', 
        'organization_id',
        'fees_is_new_addmission', 
        'is_enable',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at'
       
    ];
    
    protected $tableColumnList = [
        'id' => 'id',
        'fees_code' => 'fees_code',
        'organization_id' => 'organization_id',
        'fees_master_id' => 'fees_master_id',
        'fees_type_id' => 'fees_type_id',
        'fees_amount' => 'fees_amount',
        'fees_from_date' => 'fees_from_date',
        'fees_end_date' => 'fees_end_date',
        'fees_is_new_addmission' => 'fees_is_new_addmission',
        
        'is_enable'             => 'is_enable',
        'created_by'            => 'created_by',
        'created_at'            => 'created_at',
        'updated_by'            => 'updated_by',
        'updated_at'            => 'updated_at',
        'deleted_at'            => 'deleted_at',
        'data_user_id'          => 'data_user_id',
        
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

                'fees_amount' => 'required', 
                'fees_from_date' => 'required', 
                'fees_end_date' => 'required', 
                'fees_is_new_addmission' => 'required',                 
      

                
                'fees_type_id' =>[
                    'required', Rule::unique('fee_structure_detail', 'fees_type_id')->where(function ($query) use ($request) {
                        
                        // Start Time Validate
                        $query->where( DB::raw('DATE(fees_from_date)'), '<=', date('Y-m-d H:i:s', strtotime($request->fees_from_date)));
                        $query->where( DB::raw('DATE(fees_end_date)'), '>=', date('Y-m-d H:i:s', strtotime($request->fees_from_date)));
                     
                            $query->where('organization_id', '=', $request->organization_id);
                            $query->where('fees_master_id', '=', $request->fees_master_id);                        
                            $query->where('is_enable', '<>', '2');                     

                        }),
                    ],

                    
                    'fees_master_id' =>[
                        'required', Rule::unique('fee_structure_detail', 'fees_master_id')->where(function ($query) use ($request) {
                          
                            // End Time Validate
                            $query->where( DB::raw('DATE(fees_from_date)'), '<=', date('Y-m-d H:i:s', strtotime($request->fees_end_date)));
                            $query->where( DB::raw('DATE(fees_end_date)'), '>=', date('Y-m-d H:i:s', strtotime($request->fees_end_date)));
                            
                            $query->where('organization_id', '=', $request->organization_id);
                            $query->where('fees_type_id', '=', $request->fees_type_id);

                             $query->where('is_enable', '<>', '2');
    
                            }),
                        ],

                        'fees_from_date' =>[
                            'required', Rule::unique('fee_structure_detail', 'fees_from_date')->where(function ($query) use ($request) {
                              
                                $query->where( DB::raw('DATE(fees_end_date)'), '=', date('Y-m-d H:i:s', strtotime($request->fees_end_date)));
                               
                                $query->where('organization_id', '=', $request->organization_id);
                                $query->where('fees_type_id', '=', $request->fees_type_id);
                                $query->where('fees_master_id', '=', $request->fees_master_id);  
    
                                 $query->where('is_enable', '<>', '2');
        
                                }),
                            ],

                     
               
               
                
            ],
            'PUT' => [ 
                
            'fees_amount' => 'required', 
            'fees_from_date' => 'required', 
            'fees_end_date' => 'required', 
            'fees_is_new_addmission' => 'required',                 
  

            
            'fees_type_id' =>[
                'required', Rule::unique('fee_structure_detail', 'fees_type_id')->where(function ($query) use ($request) {
                    
                    // Start Time Validate
                    $query->where( DB::raw('DATE(fees_from_date)'), '<=', date('Y-m-d H:i:s', strtotime($request->fees_from_date)));
                    $query->where( DB::raw('DATE(fees_end_date)'), '>=', date('Y-m-d H:i:s', strtotime($request->fees_from_date)));
                    
                        // $master_data = FeeStructureMaster::find($request->fees_master_id);                          
                        $query->where('organization_id', '=', $request->organization_id);
                        $query->where('fees_master_id', '=', $request->fees_master_id);                        
                        $query->where('is_enable', '<>', '2'); 
                        $query->where('id', '<>', $request->id);                    

                    }),
                ],

                
                'fees_master_id' =>[
                    'required', Rule::unique('fee_structure_detail', 'fees_master_id')->where(function ($query) use ($request) {
                      
                        // End Time Validate
                        $query->where( DB::raw('DATE(fees_from_date)'), '<=', date('Y-m-d H:i:s', strtotime($request->fees_end_date)));
                        $query->where( DB::raw('DATE(fees_end_date)'), '>=', date('Y-m-d H:i:s', strtotime($request->fees_end_date)));
                        
                        $query->where('organization_id', '=', $request->organization_id);
                        $query->where('fees_type_id', '=', $request->fees_type_id);

                         $query->where('is_enable', '<>', '2');
                         $query->where('id', '<>', $request->id);

                        }),
                    ],

                    'fees_from_date' =>[
                        'required', Rule::unique('fee_structure_detail', 'fees_from_date')->where(function ($query) use ($request) {
                          
                            $query->where( DB::raw('DATE(fees_end_date)'), '=', date('Y-m-d H:i:s', strtotime($request->fees_end_date)));
                           
                            $query->where('organization_id', '=', $request->organization_id);
                            $query->where('fees_type_id', '=', $request->fees_type_id);
                            $query->where('fees_master_id', '=', $request->fees_master_id);  

                             $query->where('is_enable', '<>', '2');
                             $query->where('id', '<>', $request->id);
    
                            }),
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
            
            'fees_type_id.unique' => [
                "code" => 10000,                
                "message" => "Please provide unique Effective From Date"
            ],
            'fees_from_date.unique' => [
                "code" => 10000,                
                "message" => "Please provide unique Effective End Date"
            ],           

            'fees_master_id.unique' => [
                "code" => 10000,                
                "message" => "Please provide unique Date"
            ],

        ];

        $idMessages = [
            
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
