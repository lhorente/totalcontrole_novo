<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Rules\ParentExists;

class StoreWallet extends FormRequest
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

// Validator::make($data, [
//     'email' => [
//         'required',
//         Rule::exists('staff')->where(function ($query) {
//             $query->where('account_id', 1);
//         }),
//     ],
// ]);

      return [
        'titulo' => 'required|max:255',
        // 'parent_id' => 'exists:App\Models\Wallet,id',
        'parent_id' => [
            'nullable',
            'different:id', // Id da carteira pai não pode ser igual o mesmo da carteira que está sendo salva
            new \App\Rules\ParentExists(new \App\Models\Wallet), // A carteira pai precisa existir no banco de dados e pertencer ao usuário atual logado
            new \App\Rules\IsTopLevelParent(new \App\Models\Wallet) // A carteira pai precisa ser a primeira na hierarquia
          ],
        'exibir_no_saldo' => 'boolean',
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
            'titulo.required' => 'O campo título é obrigatório',
            'nome.max' => 'O título nome precisa ter no máximo 255 caracteres',
            'parent_id.different' => 'Carteira pai inválida',
            'exibir_no_saldo' => 'Erro ao salvar',
        ];
    }
}
