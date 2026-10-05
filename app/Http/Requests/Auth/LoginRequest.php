<?php

namespace App\Http\Requests\Auth;

use App\Models\Maintenance;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
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
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }

    public function checkAgencyRemoval($request): void
    {
        $user = User::where('email', $request->email)->first();

        if ($user && !$user->hasRole(['super admin', 'system admin'])) {
            $tenant = Tenant::find($user->agency_id);

            if (!$tenant) {
                throw ValidationException::withMessages([
                    'email' => 'The agency has been removed from the system. Please contact support.',
                ]);
            }
        }
    }

    public function checkActiveStatus($request): void
    {
        $user = User::where('email', $request->email)->first();

        if ($user && !$user->active_status) {
            throw ValidationException::withMessages([
                'email' => 'Your account is not active. Please contact support.',
            ]);
        }
    }

    public function checkMaintenance($request)
    {
        $maintenance = Maintenance::query()->where('status', 'enable')->first();

        $user = User::where('email', $request->email)->first();

        if ($maintenance) {
            if ($user->hasRole(['super admin', 'system admin'])) {
                return;
            }

            $dateStart = Carbon::parse($maintenance->date_start)->format('d/m/Y h:i A');
            $dateEnd = Carbon::parse($maintenance->date_end)->format('d/m/Y h:i A');

            throw ValidationException::withMessages([
                'email' => "The system is under maintenance from\n {$dateStart} to {$dateEnd}",
            ]);
        }
    }
}
