<?php

namespace AcceptCode\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationAcceptAccountRequest extends FormRequest
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
            'login' => [
                'required',
                Rule::when(
                    $this->type === 'phone',
                    [$phoneRule],
                    ['email']
                )
            ],
            'code' => 'required|string',
            'type' => 'required|in:email,phone'
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
            'login.required' => 'Необходимо указать номер телефона или email!',
            'code.required' => 'Необходимо ввести код!',
        ];
    }
}
