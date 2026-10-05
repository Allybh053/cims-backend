<?php

namespace App\Http\Requests\User;

use App\Models\Profile;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {

        $user = User::find($this->id);

        $tenant = Tenant::find($user->agency_id);

        if ($tenant) {
            tenancy()->initialize($tenant);
        }

        $userTenant = User::whereEmail($user->email)->first();

        $profile = Profile::where('user_id', $userTenant->id)->first();

        return [
            'email' => ['unique:users,email,' . $userTenant->id],
            'id_no' => ['unique:profiles,id_no,' . $profile->id],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id_no.unique' => 'The ID No. has already been taken',
        ];
    }
}
