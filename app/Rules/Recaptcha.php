<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Recaptcha implements ValidationRule
{
    private const MIN_SCORE = 0.5;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secret = config('services.recaptcha.secret_key');

        // Not configured yet (local/dev) — skip rather than block registration.
        if (! $secret) {
            return;
        }

        if (! is_string($value) || $value === '') {
            $fail('Please try again.');
            return;
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $value,
            ])->json();
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA verification request failed.', ['error' => $e->getMessage()]);
            $fail('Please try again.');
            return;
        }

        $success = $response['success'] ?? false;
        $score = $response['score'] ?? 0;
        $action = $response['action'] ?? null;

        if (! $success || $action !== 'register' || $score < self::MIN_SCORE) {
            $fail('Please try again.');
        }
    }
}
