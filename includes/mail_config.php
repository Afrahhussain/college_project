<?php
// includes/mail_config.php
// Basic config. For production use SMTP/PHPMailer — this is a simple switch.
return [
    'from_email' => 'no-reply@campusconnect.local',
    'from_name'  => 'Campus Connect',
    // If you want to enable SMTP replace with proper credentials and update send_mail() to use SMTP.
    'smtp' => [
        'enabled' => false,
        'host' => '',
        'port' => 587,
        'username' => '',
        'password' => '',
        'secure' => 'tls' // 'ssl' or 'tls'
    ]
];
