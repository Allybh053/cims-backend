<?php

namespace App\Http\Requests\Soap;

use Illuminate\Foundation\Http\FormRequest;

class CheckSSMRequest extends FormRequest
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
        return [
            // 'check_owner_id_number' => ['required'],
            'registration_number' => ['required'],
        ];
    }
}
