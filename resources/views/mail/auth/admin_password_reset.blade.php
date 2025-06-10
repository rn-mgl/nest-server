<x-mail::message>
# Nest Password Reset Link

We received a request to reset your password. Click the link below to set a new one:

<x-mail::button :url="config('app.nest_url') . '/control/reset/' . $token">
Reset Password
</x-mail::button>

This link will expire in 30 minutes. If you didn’t request this, please ignore this message.

Regards,<br>
{{ config('app.name') }}
</x-mail::message>
