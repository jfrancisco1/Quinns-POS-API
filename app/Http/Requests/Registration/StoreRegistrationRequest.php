<?php

namespace App\Http\Requests\Registration;

use App\Rules\Recaptcha;
use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'business_name' => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'unique:tenants,email'],
            'phone'         => ['nullable', 'string', 'max:20', 'unique:tenants,phone'],
            'address'       => ['nullable', 'string', 'max:500'],
            'owner_name'    => ['required', 'string', 'max:255'],
        ];

        // Only enforced once RECAPTCHA_SECRET_KEY is configured, so local
        // dev isn't blocked before real keys are set up.
        if (config('services.recaptcha.secret_key')) {
            $rules['g-recaptcha-response'] = ['required', 'string', new Recaptcha];
        }

        return $rules;
    }
}
