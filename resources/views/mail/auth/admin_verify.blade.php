<x-mail::message>
# Nest Account Verification

Welcome to Nest! We’re excited to have you join our team. To complete your account setup and gain access to your employee dashboard, please verify your email by clicking the link below:

<x-mail::button :url="config('app.nest_url') . '/control/verify/' . $token">
Verify Account
</x-mail::button>

This link will expire in 24 hours. If you didn’t create this account, please ignore this message.

Regards,<br>
{{ config('app.name') }}
</x-mail::message>
