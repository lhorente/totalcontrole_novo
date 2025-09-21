<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class IsTopLevelParent implements Rule
{
    private $model;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
     public function __construct($model)
     {
         $this->model = $model;
     }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->model->whereNull('parent_id')->where('id',$value)->count();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Elemento pai precisa ser do primeiro nível';
    }
}
