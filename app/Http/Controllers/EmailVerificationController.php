<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function emailVerification(Request $request)
    {
        $current_time = Carbon::now()->timestamp;
        $expires_time = $request->expires;
        $sha1_hashed_email = $request->hash;

        $user = User::find($request->id);

        if ($this->isEmailExpired($expires_time, $current_time)) {
            return $this->handleEmailExpired();
        }

        if (! $this->isValidCredentials($user, $sha1_hashed_email)) {
            return $this->handleInvalidCredentials();
        }

        $user->markEmailAsVerified();
        return $this->handleEmailVerified();
    }

    private function isEmailExpired($expires_time, $current_time)
    {
        return ! ($expires_time > $current_time);
    }

    private function isValidCredentials($user, $sha1_hashed_email)
    {
        return $user && sha1($user->email) == $sha1_hashed_email;
    }

    private function handleEmailExpired()
    {
        Notification::make()
        ->title('Email has expired. Please try to login to get the verification email.')
        ->danger()
        ->send();
        return redirect('/admin/login');
    }

    private function handleInvalidCredentials()
    {
        Notification::make()
        ->title('Invalid credentials.')
        ->danger()
        ->send();
        return redirect('/admin/login');
    }

    private function handleEmailVerified()
    {
        Notification::make()
        ->title('Email verified successfully. Please login.')
        ->success()
        ->send();
        return redirect('/admin/login');
    }
}
