<?php

namespace App\Listeners;

use App\Events\AdminRegistered;
use App\Mail\AccountVerification;
use App\Mail\AdminAccountVerification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class AdminEmailVerification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AdminRegistered $event): void
    {
        if ($event->admin instanceof MustVerifyEmail && !$event->admin->hasVerifiedEmail()) {
            Mail::to($event->admin)->send(new AdminAccountVerification($event->token));
        }
    }
}
