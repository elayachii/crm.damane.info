<?php

declare(strict_types=1);

namespace App\Http\Responses\Auth;

use App\Support\AuthFlowTrace;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

final class FilamentLoginResponse implements LoginResponse
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        AuthFlowTrace::info('login_response.before_forget_intended', [
            'target_url' => Filament::getUrl(),
        ]);

        $request->session()->forget('url.intended');

        AuthFlowTrace::info('login_response.after_forget_intended', [
            'target_url' => Filament::getUrl(),
        ]);

        return redirect()->to(Filament::getUrl());
    }
}
