<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategory extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
      return [
        'nome' => 'required|max:255',
        'icon_class' => ['nullable', 'max:255', 'regex:/^[a-z0-9 -]+$/'],
      ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nome.required' => 'O campo nome é obrigatório',
            'nome.max' => 'O campo nome precisa ter no máximo 255 caracteres',
            'icon_class.max' => 'O ícone precisa ter no máximo 255 caracteres',
            'icon_class.regex' => 'Ícone inválido',
        ];
    }
}
