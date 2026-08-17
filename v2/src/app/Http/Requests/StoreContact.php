<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContact extends FormRequest
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
          'tipo' => 'required|in:fornecedor,cliente,familiar,pessoal,outro',
          'status' => 'required|in:ativo,inativo',
          'documento' => 'nullable|max:32',
          'email' => 'nullable|email|max:255',
          'telefone' => 'nullable|max:32',
          'observacoes' => 'nullable|string',

          'tipo_servico' => 'nullable|max:255',
          'razao_social' => 'nullable|max:255',
          'cnpj' => 'nullable|max:32',
          'contato_responsavel' => 'nullable|max:255',
          'forma_pagamento_preferida' => 'nullable|max:255',
          'observacoes_fornecedor' => 'nullable|string',

          'valor_hora' => 'nullable|numeric|min:0',
          'forma_cobranca' => 'nullable|max:255',
          'contrato_url' => 'nullable|url|max:255',
          'observacoes_cliente' => 'nullable|string',
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
            'tipo.required' => 'O campo tipo é obrigatório',
            'tipo.in' => 'O campo tipo é inválido',
            'status.required' => 'O campo status é obrigatório',
            'status.in' => 'O campo status é inválido',
            'email.email' => 'O campo e-mail é inválido',
            'valor_hora.numeric' => 'O campo valor/hora é inválido',
            'valor_hora.min' => 'O campo valor/hora não pode ser negativo',
            'contrato_url.url' => 'O campo link do contrato precisa ser uma URL válida',
        ];
    }
}
