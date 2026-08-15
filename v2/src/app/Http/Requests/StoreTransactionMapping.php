<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionMapping extends FormRequest
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
        'padrao'          => 'required|max:255',
        'descricao_local' => 'required|max:255',
        'id_categoria'    => 'nullable|integer',
        'id_cliente'      => 'nullable|integer',
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
            'padrao.required'          => 'O campo padrão da fatura é obrigatório',
            'padrao.max'               => 'O campo padrão da fatura precisa ter no máximo 255 caracteres',
            'descricao_local.required' => 'O campo local/apelido é obrigatório',
            'descricao_local.max'      => 'O campo local/apelido precisa ter no máximo 255 caracteres',
        ];
    }
}
