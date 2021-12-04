<?php

namespace App\Models;

use App\Config\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Utilities\Utilities;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{

    protected $table = 'languages';
    protected $primaryKey = 'lang_code';
    protected $keyType = 'string';

    public $incrementing = false;
    public $timestamps  = false;

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lang_code',
        'lang_name',
        'lang_dir',
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
        'name' => 'lang_name',
        'lang' => 'lang_code',
        'dir' => 'lang_dir',
        'activate' => 'is_enable',
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

        return "lang_code";
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
               
                'name' => [
                    'required', Rule::unique('languages', 'lang_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                    }),
                    'min:3', 'max:100' //'alpha_space', 
                ],
                'lang' => [
                    'required', Rule::unique('languages', 'lang_code')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2');
                    }),
                    'min:3', 'max:3', 'alpha', 
                ],
                'dir' => 'required|between:0,1'
            ],
            'PUT' => [
                'lang' => [
                    'required', Rule::unique('languages', 'lang_code')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                        ->where('id', '<>', $request->id);
                    }),
                    'min:3', 'max:3', 'alpha', 
                ],
                'name' => [
                    'required', Rule::unique('languages', 'lang_name')->where(function ($query) use ($request) {
                        $query->where('is_enable', '<>', '2')
                            ->where('lang_code', '<>', $request->lang)
                            ->where('id', '<>', $request->id);
                    }),
                    'min:3', 'max:100' //'alpha_space', 
                ],
                'dir' => 'required|between:0,1'
            ],
            'PATCH' => [
                'lang' => 'required|alpha|min:2|max:3',
                'activate' => 'required|numeric|between:0,1'
            ],
            'DELETE' => [
                'lang' => 'required|alpha|min:2|max:3',
            ],
            'GET_ONE' => ['lang' => 'required|alpha|min:2|max:3'],
            'GET_ALL' => []
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
            'lang.required' => [
                "code" => 10107,
                "message" => "Please provide language code."
            ],
            'lang.alpha' => [
                "code" => 10106,
                "message" => "Id must be an string."
            ],
            'lang.unique' => [
                "code" => 10102,
                "message" => "Please provide unique language code."
            ],
            'lang.min' => [
                "string" => [
                    "code" => 10106,
                    "message" => "The language code may not be greater than :max characters."
                ]
            ],
            'lang.max' => [
                "string" => [
                    "code" => 10106,
                    "message" => "The language code must be at least :min characters."
                ]
            ],
            'name.required' => [
                "code" => 10101,
                "message" => "Please provide language name."
            ],
            'name.unique' => [
                "code" => 10102,
                "message" => "Please provide unique language name."
            ],
            'name.alpha_space' => [
                "code" => 10103,
                "message" => "The language name may only contain letters and spaces."
            ],
            'name.min' => [
                "string" => [
                    "code" => 10104,
                    "message" => "The language name must be at least :min characters."
                ]
            ],
            'name.max' => [
                "string" => [
                    "code" => 10105,
                    "message" => "The language name may not be greater than :max characters."
                ]
            ],

            'dir.required' => [
                "code" => 10101,
                "message" => "Please provide language direction."
            ],
            'dir.between' => [
                "code" => 10102,
                "message" => "The language direction must be 0 or 1."
            ],

        ];

        $idMessages = [
            'lang.required' => [
                "code" => 10107,
                "message" => "Please provide language code."
            ],
            'lang.alpha' => [
                "code" => 10106,
                "message" => "Id must be an string."
            ],
            'lang.unique' => [
                "code" => 10102,
                "message" => "Please provide unique language code."
            ],
            'lang.min' => [
                "string" => [
                    "code" => 10106,
                    "message" => "The language code may not be greater than :max characters."
                ]
            ],
            'lang.max' => [
                "string" => [
                    "code" => 10106,
                    "message" => "The language code must be at least :min characters."
                ]
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
