<?php

namespace App\Http\Requests;

use App\Models\CreditCard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
          'id_cartao_pai' => [
            'nullable',
            Rule::exists('cartoes', 'id')->where(function ($query) {
              $query->where('id_usuario', Auth::id())
                    ->whereNull('id_cartao_pai');
            }),
            function ($attribute, $value, $fail) {
              $id = $this->input('id');
              if ($value && $id && CreditCard::where('id_cartao_pai', $id)->exists()) {
                $fail('Este cartão já possui subcartões vinculados e não pode virar um subcartão.');
              }
            },
          ],
          'ultimos_digitos' => 'nullable|digits:4',
          'id_categoria_padrao' => 'nullable|exists:categorias,id',
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
            'dia_vencimento.between' => 'Dia de vencimento inválido',
            'id_cartao_pai.exists' => 'Cartão pai inválido',
            'ultimos_digitos.digits' => 'Os últimos dígitos devem ter exatamente 4 números',
            'id_categoria_padrao.exists' => 'Categoria padrão inválida',
          ];
    }
}
