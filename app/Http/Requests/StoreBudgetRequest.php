<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autorização por user_id feita no controller
    }

    public function rules(): array
    {
        return [
            'work_type'   => 'required|in:residential,commercial,renovation,industrial',
            'area_m2'     => 'required|numeric|min:10|max:50000',
            'standard'    => 'required|in:simple,medium,high',
            'state'       => 'required|string|size:2',
            'description' => 'nullable|string|max:1000',
            'bdi_percent' => 'nullable|numeric|min:0|max:100',
            'title'       => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'work_type.required' => 'Selecione o tipo de obra.',
            'work_type.in'       => 'Tipo de obra inválido.',
            'area_m2.required'   => 'Informe a área total da obra.',
            'area_m2.numeric'    => 'A área deve ser um número.',
            'area_m2.min'        => 'A área mínima é de 10 m².',
            'area_m2.max'        => 'A área máxima é de 50.000 m².',
            'standard.required'  => 'Selecione o padrão construtivo.',
            'state.required'     => 'Selecione o estado da obra.',
            'state.size'         => 'Use a sigla do estado (ex: MS, SP).',
        ];
    }
}
