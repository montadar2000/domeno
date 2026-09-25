<?php

namespace App\Http\Requests;

use App\Enums\TeamSide;
use App\Services\ScoringService;
use App\Support\EnglishDigits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'points' => EnglishDigits::from($this->input('points')),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'team' => ['required', Rule::enum(TeamSide::class)],
            'points' => ['required', 'integer', 'min:1', 'max:'.ScoringService::MAX_POINTS],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'team.required' => 'اختر الفريق.',
            'team.enum' => 'اختر فريقًا صحيحًا.',
            'points.required' => 'اكتب النقاط أولًا.',
            'points.integer' => 'النقاط لازم تكون رقمًا صحيحًا.',
            'points.min' => 'أقل تسجيل هو نقطة واحدة.',
            'points.max' => 'أقصى تسجيل هو 200 نقطة.',
        ];
    }
}
