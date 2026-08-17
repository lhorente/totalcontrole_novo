<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumento extends FormRequest
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
        'tipo'              => 'required|in:seguro,ipva,cnh,garantia,contrato,outro',
        'titulo'            => 'required|max:255',
        'data_evento'       => 'required|date',
        'data_vencimento'   => 'nullable|date',
        'id_cliente'        => 'nullable|integer',
        'valor'             => 'nullable|numeric|min:0',
        'status'            => 'required|in:ativo,concluido,cancelado,vencido',
        'numero_documento'  => 'nullable|max:255',
        'emissor'           => 'nullable|max:255',
        'arquivo_url'       => 'nullable|url|max:255',
        'observacoes'       => 'nullable|string',
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
            'tipo.required'            => 'O campo tipo é obrigatório',
            'tipo.in'                  => 'O campo tipo é inválido',
            'titulo.required'          => 'O campo título é obrigatório',
            'titulo.max'               => 'O campo título precisa ter no máximo 255 caracteres',
            'data_evento.required'     => 'O campo data é obrigatório',
            'data_evento.date'         => 'O campo data é inválido',
            'data_vencimento.date'     => 'O campo data de vencimento é inválido',
            'valor.numeric'            => 'O campo valor é inválido',
            'valor.min'                => 'O campo valor não pode ser negativo',
            'status.required'          => 'O campo status é obrigatório',
            'status.in'                => 'O campo status é inválido',
            'arquivo_url.url'          => 'O link do documento precisa ser uma URL válida (ex: https://drive.google.com/...)',
            'arquivo_url.max'          => 'O link do documento precisa ter no máximo 255 caracteres',
        ];
    }
}
