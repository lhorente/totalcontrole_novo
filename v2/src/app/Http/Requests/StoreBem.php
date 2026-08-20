<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBem extends FormRequest
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
        'tipo'    => 'required|in:casa,carro,outro',
        'nome'    => 'required|max:255',
        'detalhe' => 'nullable|max:255',
        'ativo'   => 'nullable|boolean',
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
            'tipo.required'  => 'O campo tipo é obrigatório',
            'tipo.in'        => 'O campo tipo é inválido',
            'nome.required'  => 'O campo nome é obrigatório',
            'nome.max'       => 'O campo nome precisa ter no máximo 255 caracteres',
            'detalhe.max'    => 'O campo detalhe precisa ter no máximo 255 caracteres',
        ];
    }
}
