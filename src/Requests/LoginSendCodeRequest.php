<?php

namespace AcceptCode\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginSendCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $phoneRuleFactory = config('accept-code.phone_validation_rule');
        $phoneRule = call_user_func($phoneRuleFactory);

        return [
            'phone' => ['required', $phoneRule],
            'isPortal' => 'required|boolean',
            'captchaToken' => 'required|string|min:1',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string>
     */
    public function messages()
    {
        return [
            'phone.required' => 'Необходимо указать номер телефона!',
            'captchaToken.required' => 'Необходимо пройти проверку на то, что вы не робот!',
        ];
    }
}
