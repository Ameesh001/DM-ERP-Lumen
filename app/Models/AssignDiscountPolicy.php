<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class AssignDiscountPolicy extends Model
{
    // use HasFactory;

    protected $table = 'assign_discount_policy';

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
        'disc_code', 
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
        'disc_code' => 'disc_code',
        'data_org_id' => 'organization_id',
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
               
                'disc_code' => 'required',              
                


                'disc_code' =>[
                    'required', Rule::unique('assign_discount_policy', 'disc_code')->where(function ($query) use ($request) {
                        
                        // $query->where( DB::raw('DATE(disc_from_date)'), '<=', date('Y-m-d H:i:s', strtotime($request->disc_from_date)));
                        // $query->where( DB::raw('DATE(disc_end_date)'), '>=', date('Y-m-d H:i:s', strtotime($request->disc_from_date)));
                        
                        // $query->where('organization_id', '=', $request->org_id);

                         $org_list = Organization::find($request->data_org_id);

                         $data = $request->all();

                         if(isset($request->admission_code)) {

                            $data_admission_code = $data['admission_code'];


                            $query->where(function($querys) use ($data_admission_code) {

                                foreach($data_admission_code as $array){                               
                                            
                                    $code_admission = substr($array, strpos($array, "_") + 1); 
                                    
                                    // $query->where('admission_codes', '=', $code_admission); 
                                    $querys->orWhere('admission_code', '=', $code_admission);
                                        
                                }    
                            
                            });

                         $query->where('is_enable', '<>', '2');
                            


                         }

                         else{

                            $query->where('country_id', '=', $org_list->countries_id);
                            $query->where('state_id', '=', $request->state_id);
                            $query->where('region_id', '=', $request->region_id);
                            $query->where('city_id', '=', $request->city_id);
                            $query->where('campus_id', '=', $request->campus_id);
                            $query->where('class_id', '=', $request->class_id);
                            $query->where('is_enable', '<>', '2');

                         }




                        }),
                    ],

                 
                    
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
            
            'disc_code.unique' => [
                "code" => 10000,
                "message" => "Hierarchy already exits"
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
