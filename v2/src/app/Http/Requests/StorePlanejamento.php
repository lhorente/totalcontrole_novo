<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanejamento extends FormRequest
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
        'tipo'                    => 'required|in:manutencao,compra',
        'titulo'                  => 'required|max:255',
        'id_bem'                  => 'nullable|integer',
        'categoria'               => 'nullable|max:255',
        'prioridade'              => 'required|in:necessidade,desejo',
        'data_vencimento'         => 'nullable|date',
        'valor'                   => 'nullable|numeric|min:0',
        'status'                  => 'required|in:planejado,agendado,concluido,cancelado',
        'recorrente'              => 'nullable|boolean',
        'recorrencia_intervalo'   => 'nullable|integer|min:1|required_if:recorrente,1',
        'recorrencia_unidade'     => 'nullable|in:meses,anos|required_if:recorrente,1',
        'observacoes'             => 'nullable|string',
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
            'tipo.required'                  => 'O campo tipo é obrigatório',
            'tipo.in'                        => 'O campo tipo é inválido',
            'titulo.required'                => 'O campo título é obrigatório',
            'titulo.max'                     => 'O campo título precisa ter no máximo 255 caracteres',
            'prioridade.required'            => 'O campo prioridade é obrigatório',
            'prioridade.in'                  => 'O campo prioridade é inválido',
            'data_vencimento.date'           => 'O campo data prevista é inválido',
            'valor.numeric'                  => 'O campo valor estimado é inválido',
            'valor.min'                      => 'O campo valor estimado não pode ser negativo',
            'status.required'                => 'O campo status é obrigatório',
            'status.in'                      => 'O campo status é inválido',
            'recorrencia_intervalo.required_if' => 'Informe a cada quantos meses/anos o item se repete',
            'recorrencia_unidade.required_if'   => 'Informe se a recorrência é em meses ou anos',
        ];
    }
}
