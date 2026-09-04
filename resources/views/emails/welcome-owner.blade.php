<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <p>Hi {{ $businessName }},</p>

    <p>Your account is ready. Here are your login credentials:</p>

    <table cellpadding="6" cellspacing="0" style="border-collapse: collapse; margin: 16px 0;">
        <tr>
            <td style="font-weight: bold;">Username:</td>
            <td>{{ $username }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Temporary password:</td>
            <td>{{ $temporaryPassword }}</td>
        </tr>
    </table>

    <p>For security, you'll be asked to set a new password the first time you log in.</p>

    <p><a href="{{ $loginUrl }}">Log in to your account</a></p>

    <p>Your account starts on a 30-day free trial. You can upgrade your plan at any time before the trial ends to keep using the app without interruption.</p>
</body>
</html>
