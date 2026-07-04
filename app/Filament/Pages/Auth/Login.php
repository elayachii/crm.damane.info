<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Support\AuthFlowTrace;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Throwable;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        AuthFlowTrace::info('login.authenticate.before');

        try {
            $response = parent::authenticate();
        } catch (Throwable $exception) {
            AuthFlowTrace::info('login.authenticate.exception', [
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        AuthFlowTrace::info('login.authenticate.after', [
            'response_class' => $response ? $response::class : null,
        ]);

        return $response;
    }
}
