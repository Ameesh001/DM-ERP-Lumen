<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class AssignFeeStructure extends Model
{
    // use HasFactory;

    protected $table = 'assign_fee_structure';

    public $timestamps  = false;

    public function Country()
    {
        return $this->belongsTo(Country::class, 'country_id')->select('id','country_name');
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
        return $this->belongsTo(Classes::class, 'class_id')->select('id','class_name');
    }

    public function Student()
    {
        return $this->belongsTo(StudentAdmission::class, 'student_id')->select('id','student_name');
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'fees_code', 
        'organization_id', 
        'country_id', 
        'state_id', 
        'region_id', 
        'city_id', 
        'campus_id', 
        'class_id', 
        'student_id',
        'admission_code', 
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
        'country_id' => 'country_id',
        'state_id' => 'state_id',
        'region_id' => 'region_id',
        'city_id' => 'city_id',
        'campus_id' => 'campus_id',
        'class_id' => 'class_id',
        'student_id' => 'student_id',
        'admission_code' => 'admission_code',
        
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

                // 'fees_code' => 'required',
                // 'country_id' => 'required',
                // 'fees_code' =>[
                //     'required', Rule::unique('assign_fee_structure', 'fees_code')->where(function ($query) use ($request) {
                        

                      
                      
                //         if(empty($request->admission_code)){

                //             $query->where('organization_id', '=', $request->organization_id);
                //              $query->where('country_id', '=', $request->country_id);
                //              $query->where('is_enable', '<>', '2');     

                //         }

                //         else{

                           
                //                 if(isset($request->admission_code)) { 
                //                    $admission_code_arr = $request->admission_code;

                //                    $query->where(function($query) use ($admission_code_arr) {
                //                         foreach ($admission_code_arr as $key => $value) {

                //                             $admission_code = substr($value, strpos($value, "_") + 1); 
                //                             $query->orWhere('admission_code', '=', $admission_code);
                //                         }
                //                     });

                //                 }
                            

                //             $query->where('organization_id', '=', $request->organization_id);
                //             $query->where('is_enable', '<>', '2');

                //         }
                       
                     
                    
                //         }),
                //     ],

                //     'organization_id' =>[
                //         'required', Rule::unique('assign_fee_structure', 'organization_id')->where(function ($query) use ($request) {
                            
    
    
                               
                //                     if($request->admission_code != null) { 
                //                         $admission_code_arr = $request->admission_code;
    
                //                         $query->where(function($query) use ($admission_code_arr) {
                //                              foreach ($admission_code_arr as $key => $value) {
     
                //                                  $admission_code = substr($value, strpos($value, "_") + 1); 
                //                                  $query->orWhere('admission_code', '=', $admission_code);
                //                              }
                //                          });    
                //                     }    


                                          
                //                 //    else if($request->class_id != null) { 
                                

                //                 //     $query->orWhere('class_id', '=', $request->class_id);
                //                 //     $query->orWhere('campus_id', '=', $request->campus_id);
                //                 //     } 

                //                 //     else if($request->campus_id != null) { 

                //                 //         $query->orWhere('campus_id', '=', $request->campus_id);

                //                 //         }

                //                 //         else if($request->city_id != null) { 

                //                 //             $query->orWhere('city_id', '=', $request->city_id);
    
                //                 //             }
                                            
                //                 //             else if($request->region_id != null) { 

                //                 //                 $query->orWhere('region_id', '=', $request->region_id);
        
                //                 //                 }
                //                 //                 else if($request->state_id != null) { 

                //                 //                     $query->orWhere('state_id', '=', $request->state_id);
            
                //                 //                     }
                //                 //                     else if($request->country_id != null) { 

                //                 //                         $query->orWhere('country_ids', '=', $request->country_id);
                
                //                 //                         }
                                    
                //                     else{
                //                         $query->where('organization_id', '=', 1000);

                //                     }
    
                                
                //                 $query->where('is_enable', '<>', '2');
    
                            
                           
                         
                        
                //             }),
                //         ],


               
                
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

            'fees_code.unique' => [
                "code" => 10000,
                "message" => "Fee Code already assigned"
            ],

            'organization_id.unique' => [
                "code" => 10000,
                "message" => "Student(s) already assigned"
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
