<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportCsvRequest extends FormRequest
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
            'file' => 'required|file|mimes:csv,txt',
            'id_cartao' => 'required|integer|exists:cartoes,id',
            'data_fatura' => 'required|date',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'file.required' => 'O arquivo é obrigatório.',
            'file.file' => 'O arquivo enviado é inválido.',
            'file.mimes' => 'O arquivo deve ser do tipo CSV ou TXT.',
            'id_cartao.required' => 'O ID do cartão é obrigatório.',
            'id_cartao.integer' => 'O ID do cartão deve ser um número inteiro.',
            'id_cartao.exists' => 'O cartão selecionado não existe.',
            'data_fatura.required' => 'A data da fatura é obrigatória.',
            'data_fatura.date' => 'A data da fatura deve ser uma data válida.',
        ];
    }
}
