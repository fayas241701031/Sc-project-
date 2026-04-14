<?php
require_once __DIR__ . '/config.php';

// Check if PHPMailer exists (either via Composer vendor/ or manual path)
$composer_path = dirname(__DIR__) . '/vendor/autoload.php';
$manual_path = __DIR__ . '/PHPMailer/src/PHPMailer.php';

if (file_exists($composer_path)) {
    require_once $composer_path;
} elseif (file_exists($manual_path)) {
    require_once __DIR__ . '/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Sends a verification email to the user.
 * @param string $to Recipient email
 * @param string $name Recipient name
 * @param string $token Verification token
 * @return bool Success status
 */
function sendVerificationEmail($to, $name, $token) {
    // If PHPMailer class is not loaded, we log a warning
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $msg = "CRITICAL: PHPMailer library missing! Please install via composer or download to includes/PHPMailer/";
        error_log($msg);
        return "Library Missing: $msg"; // Return string error for UI
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = str_replace(' ', '', SMTP_PASS); // Remove spaces from App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_USER, 'Bazaar Marketplace');
        $mail->addAddress($to, $name);

        // Content
        $verify_link = BASE_URL . "/verify.php?token=" . $token;
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Bazaar Account';
        $mail->Body    = "
            <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                <h2 style='color: #007aff;'>Welcome to Bazaar!</h2>
                <p>Hello $name,</p>
                <p>Thank you for joining our premium marketplace. Please click the button below to verify your email address. <strong>This link will expire in 5 minutes.</strong></p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$verify_link' style='background: #007aff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: bold;'>Verify Email Address</a>
                </div>
                <p style='color: #888; font-size: 0.9rem;'>If you did not create an account, please ignore this email.</p>
                <hr style='border: 0; border-top: 1px solid #eee;'>
                <p style='font-size: 0.8rem; color: #aaa;'>&copy; " . date('Y') . " Bazaar eCommerce. All rights reserved.</p>
            </div>
        ";
        $mail->AltBody = "Hello $name,\n\nVerify your account by clicking here: $verify_link\n\nNote: This link expires in 5 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        $error = "Mailer Error: " . $mail->ErrorInfo;
        error_log($error);
        return $error; // Return the specific error message
    }
}
?>
