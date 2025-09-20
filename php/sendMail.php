<?php
include 'conf.php';
require '../vendor/autoload.php'; 

function registrationEmail($toEmail, $name) {
    $mail = new PHPMailer\PHPMailer\PHPMailer();

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_USERNAME, 'PriMeri Support');
        $mail->addAddress($toEmail, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to PriMeri!';
        $mail->Body = "
<div style='font-family: Arial, sans-serif; background-color: #f3ecdf; padding: 20px;'>
  <div style='max-width: 600px; margin: auto; background: #ffffff; border-radius: 12px; 
              padding: 30px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1);'>

    <h2 style='color: #1f3b1f; text-align: center; margin-bottom: 10px;'>
      Welcome to PriMeri, $name!
    </h2>

    <p style='font-size: 15px; color: #333; text-align: center; line-height: 1.6;'>
      We're so excited to have you on board 🎉<br><br>
      PriMeri is more than just a platform, it's a community built to support and grow with you.  
      Get ready to explore, connect, and enjoy everything we have to offer.
    </p>

    <div style='text-align: center; margin: 25px 0;'>
      <a href='http:/localhost/primeri/html/login.html' 
         style='display: inline-block; background-color: #1f3b1f; color: #ffffff; 
                padding: 12px 24px; border-radius: 25px; text-decoration: none; 
                font-size: 15px; font-weight: bold;'>
        Get Started
      </a>
    </div>

    <p style='font-size: 14px; color: #555; text-align: center; line-height: 1.6;'>
      If you have any questions, we’re always here to help.<br>
      Just hit reply or reach us at <a href='mailto:tabitha.sila@strathmore.edu' 
      style='color: #2f5a2f; text-decoration: none;'>support@primeri.com</a>.
    </p>

    <p style='text-align: center; font-size: 14px; color: #777; margin-top: 30px;'>
      Best regards,<br>
      <strong style='color: #1f3b1f;'>The PriMeri Team</strong>
    </p>

  </div>
</div>
";
        $mail->send();
 
    } catch (Exception $e) {
    }
}

?>