<?php

namespace AcceptCode\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginAcceptCodeRequest extends FormRequest
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
            'code' => 'required|string',
            'isPortal' => 'required|boolean'
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
            'code.required' => 'Необходимо ввести код!',
        ];
    }
}
