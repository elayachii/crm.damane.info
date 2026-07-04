<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\AuthFlowTrace;
use Filament\Http\Middleware\Authenticate;
use Throwable;

class FilamentAuthenticate extends Authenticate
{
    /**
     * @param array<string> $guards
     */
    protected function authenticate($request, array $guards): void
    {
        AuthFlowTrace::info('middleware.authenticate.before', [
            'guards' => $guards,
        ]);

        try {
            parent::authenticate($request, $guards);
        } catch (Throwable $exception) {
            AuthFlowTrace::info('middleware.authenticate.exception', [
                'guards' => $guards,
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        AuthFlowTrace::info('middleware.authenticate.after', [
            'guards' => $guards,
        ]);
    }

    protected function redirectTo($request): ?string
    {
        $redirectTo = parent::redirectTo($request);

        AuthFlowTrace::info('middleware.redirect_to_login', [
            'redirect_to' => $redirectTo,
        ]);

        return $redirectTo;
    }
}
