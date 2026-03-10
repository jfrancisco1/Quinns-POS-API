<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function render($request, Throwable $e)
    {
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
        }

        return parent::render($request, $e);
    }

    public function register(): void
    {
        $this->renderable(function (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[0] ?? null;

            // 23505 = unique violation in PostgreSQL
            if ($errorCode === '23505') {
                preg_match('/Key \((.+?)\)=\((.+?)\) already exists/', $e->getMessage(), $matches);

                $column = $matches[1] ?? 'field';
                $value = $matches[2] ?? '';

                return response()->json([
                    'message' => "The {$column} '{$value}' is already taken.",
                ], 422);
            }

            // 23502 = not null violation
            if ($errorCode === '23502') {
                return response()->json([
                    'message' => 'A required field is missing.',
                ], 422);
            }
        });
    }
}
