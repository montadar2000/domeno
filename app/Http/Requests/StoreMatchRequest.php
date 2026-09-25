<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'team_one_name' => ['required', 'string', 'max:40'],
            'team_two_name' => ['required', 'string', 'max:40', 'different:team_one_name'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'team_one_name.required' => 'اكتب اسم الفريق الأول.',
            'team_two_name.required' => 'اكتب اسم الفريق الثاني.',
            'team_one_name.max' => 'اسم الفريق ما يزيد عن 40 حرف.',
            'team_two_name.max' => 'اسم الفريق ما يزيد عن 40 حرف.',
            'team_two_name.different' => 'الفريقين لازم أسماؤهم تختلف.',
        ];
    }
}
