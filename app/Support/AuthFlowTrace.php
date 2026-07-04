<?php

declare(strict_types=1);

namespace App\Support;

use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

final class AuthFlowTrace
{
    /**
     * @param array<string, mixed> $extra
     */
    public static function info(string $step, array $extra = []): void
    {
        Log::info('filament_auth_flow.'.$step, array_merge(self::context(), $extra));
    }

    /**
     * @return array<string, mixed>
     */
    private static function context(): array
    {
        $request = self::request();

        return [
            'authenticated_user_id' => self::authenticatedUserId(),
            'auth_check' => self::safe(fn (): bool => Auth::check()),
            'auth_id' => self::safe(fn (): mixed => Auth::id()),
            'filament_auth_check' => self::safe(fn (): bool => Filament::auth()->check()),
            'filament_auth_id' => self::safe(fn (): mixed => Filament::auth()->id()),
            'session_id' => self::sessionId($request),
            'previous_url' => self::safe(fn (): ?string => url()->previous()),
            'intended_url' => self::intendedUrl($request),
            'path' => $request?->path(),
            'method' => $request?->method(),
        ];
    }

    private static function authenticatedUserId(): mixed
    {
        return self::safe(fn (): mixed => Auth::user()?->getAuthIdentifier());
    }

    private static function intendedUrl(?Request $request): mixed
    {
        return self::safe(static function () use ($request): mixed {
            if (! $request?->hasSession()) {
                return null;
            }

            return $request->session()->get('url.intended');
        });
    }

    private static function sessionId(?Request $request): ?string
    {
        return self::safe(static function () use ($request): ?string {
            if (! $request?->hasSession()) {
                return null;
            }

            return $request->session()->getId();
        });
    }

    private static function request(): ?Request
    {
        return self::safe(static function (): ?Request {
            if (! app()->bound('request')) {
                return null;
            }

            $request = request();

            return $request instanceof Request ? $request : null;
        });
    }

    /**
     * @template TValue
     *
     * @param callable(): TValue $callback
     * @return TValue|null
     */
    private static function safe(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (Throwable) {
            return null;
        }
    }
}
