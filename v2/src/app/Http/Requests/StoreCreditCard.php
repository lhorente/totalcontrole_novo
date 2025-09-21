<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditCard extends FormRequest
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
          'descricao' => 'required|max:255',
          'dia_vencimento' => 'required|numeric|between:1,31',
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
            'descricao.required' => 'O campo descrição é obrigatório',
            'descricao.max' => 'O campo descrição precisa ter no máximo 255 caracteres',
            'dia_vencimento.required' => 'O campo dia de vencimento é obrigatório',
            'dia_vencimento.between' => 'Dia de vencimento inválido'
          ];
    }
}
