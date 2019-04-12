<?php
/* ------------------------------------------------------------------------------------------------
   mail_lib.php
   
   ------------------------------------------------------------------------------------------------
*/



function sendEmail($format, $from, $fromtxt, $reply, $replytxt, $email_to, $email_cc, $email_bc, $attachments, $subject, $body)
/* ---------------------
sends email via external smtp server
*/
{
    $status = 0;
    $num_address = 0;
    $num_attach = 0;
    
    # create mail object
    $mail = new PHPMailer();  
    
    # turn off error messages
    $mail->SMTPDebug = false;
    $mail->do_debug = 0;
 
    $mail->IsSMTP();  // telling the class to use SMTP
    $mail->Mailer = "smtp";
    $mail->Host = $_SESSION['smtp_host'];     // ssl://smtp.gmail.com;
    $mail->Port = $_SESSION['smtp_port'];     // 465
    $mail->SMTPAuth = true;                   // turn on SMTP authentication
    $mail->Username = $_SESSION['smtp_user']; // starcrossyc.email@gmail.com"; // SMTP username
    $mail->Password = $_SESSION['smtp_pwd'];  // Kent0n1940";                  // SMTP password 

    if ($reply)
    {
        $mail->AddReplyTo($reply, $replytxt);
        //$mail->AddReplyTo('mark.elkington@metoffice.gov.uk', 'Reply to Rota Manager');
    }
    if ($from)
    {
        $mail->SetFrom($from, $fromtxt);
        //$mail->SetFrom('starcrossyc.email@gmail.com', 'Message from Starcross YC');
    }
    else
    {
        // must be from address - return with error status
        $status = -1;
        return $status;
    }
    
     
    // email - to
    if (count($email_to)>=1)
    {
        foreach ($email_to as $email_address)
        {
            $num_address++;
            $mail->AddAddress($email_address);
            if ($num_address==1)
            {
                $logaddress = $email_address;
            }
        }
    }
    else
    {
        // no addressees for email - return with error status
        $status = -2;
        return $status;
    }
   
    // email - cc
    if (count($email_cc)>=1)
    {
        foreach ($email_cc as $email_address)
        {
            $num_address++;
            $mail->AddCC($email_address);           
        }
    }
       
    // email - bcc
    if (count($email_bc)>=1)
    {
        foreach ($email_bc as $email_address)
        {
            $num_address++;
            $mail->AddBCC($email_address);
        }
    }

    // add attachments
    //echo "<pre>"; print_r($attachments); echo "</pre>";
    if (count($attachments)>=1)
    {
        foreach ($attachments as $attachment)
        {
            $num_attach++;
            $mail->AddAttachment($attachment);
            //$mail->AddAttachment('./test/test.png'); 
        }
    }
    
    // set format
    if ($format=="html")
    {
        $mail->IsHTML(true);
    }

    // set subject
    if ($subject)
    {
        $mail->Subject = $subject;
        //$mail->Subject  = "First PHPMailer Message";
    }
    else
    {
        // error subject must be specified
        $status = -3;
        return $status;
    }
    
    // set body
    if ($body)
    {
        $mail->Body = $body;
        //$mail->Body     = "Hi! <span style=\"color: darkred; font-size: 20pt;\">This is my second e-mail sent through PHPMailer.<span><br><small><b> hell0 world</b></small>";
    }
    else
    {
        // error subject must be specified
        $status = -4;
        return $status;
    }
 
    if(!$mail->Send())   // error when message was sent
    {               
        $status = -5;
        if ($_SESSION['logfile'])
        {
            $error =  PHP_EOL.' - Mailer error: ' . $mail->ErrorInfo;
            write_log ("email FAILED: $logaddress ($num_address addresses in total) - $subject - [$num_attach attachments] $error");
        }
    } 
    else  // message sent 
    {
        $status = 0;
        if ($_SESSION['logfile'])
        {
            write_log("email sent: $logaddress ($num_address addresses in total) - $subject - [$num_attach attachments]".PHP_EOL);
        }
    }
    
    return $status;
}    