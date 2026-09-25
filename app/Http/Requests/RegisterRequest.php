<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:40', 'unique:users,username', 'regex:/^[\p{L}\p{N}_.\-]+$/u'],
            'password' => ['required', 'string', Password::min(4), 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'اكتب اسم المستخدم.',
            'username.min' => 'اسم المستخدم أقله 3 أحرف.',
            'username.max' => 'اسم المستخدم ما يزيد عن 40 حرف.',
            'username.unique' => 'اسم المستخدم مأخوذ.',
            'username.regex' => 'اسم المستخدم حروف وأرقام فقط، بدون فراغات.',
            'password.required' => 'اكتب كلمة السر.',
            'password.min' => 'كلمة السر أقله 4 أحرف.',
            'password.confirmed' => 'تأكيد كلمة السر ما يطابق.',
        ];
    }
}
