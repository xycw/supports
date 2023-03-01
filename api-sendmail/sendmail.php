<?php
include 'lib/PHPMailerAutoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $smtp_email = $_POST['smtp_email'];
    $smtp_pwd   = $_POST['smtp_pwd'];
    $smtp_host  = $_POST['smtp_host'];
    $smtp_port  = $_POST['smtp_port'];
    
    
    $from_email_address = empty($_POST['from_email_address']) ? $smtp_email : $_POST['from_email_address'];
    $from_email_name = empty($_POST['from_email_name']) ? $smtp_email : $_POST['from_email_name'];
    $to_name = empty($_POST['to_name']) ? 'Customer' : $_POST['to_name'];
    $to_address = empty($_POST['to_address']) ? '' : $_POST['to_address'];
    $email_reply_to_address = empty($_POST['email_reply_to_address']) ? $smtp_email : $_POST['email_reply_to_address'];
   // $email_reply_to_name = empty($_POST['email_reply_to_name']) ? $smtp_email : $_POST['email_reply_to_name'];
   $email_reply_to_name=$email_reply_to_address;
    $email_subject = empty($_POST['email_subject']) ? '' : $_POST['email_subject'];
    
    $email_html = empty($_POST['email_html']) ? '' : ($_POST['email_html']);
    $email_text = empty($_POST['email_text']) ? '' : ($_POST['email_text']);
    
    if (empty($to_address)) {
        echo 'The email is empty!';
        exit;
    }

    $mail = new PHPMailer;

    //$mail->SMTPDebug = 3;                               // Enable verbose debug output

    $mail->IsHTML(true);
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host     = $smtp_host;  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = $smtp_email;                 // SMTP username
    $mail->Password = $smtp_pwd;                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = $smtp_port;                                    // TCP port to connect to
    $mail->Timeout = 120;
    
    $mail->From = $from_email_address;
    $mail->FromName = $from_email_name;
    $mail->addAddress($to_address, $to_name);     // Add a recipient
    //$mail->addReplyTo($email_reply_to_address, $email_reply_to_name);
    
    
    $mail->CharSet='utf-8';
    $mail->Encoding = '8bit';
    $mail->Subject = $email_subject;
    $mail->MsgHTML($email_html);
    
    if (!$mail->send()) {
        echo 'failure(' . $mail->ErrorInfo . ')';
    } else {
        echo 'success';
    }
}