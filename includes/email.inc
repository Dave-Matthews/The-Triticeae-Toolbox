<?php
/**
 * Download Gateway New
 *
 * PHP version 5.3
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/includes/email.inc
 *
 * The purpose of this script is to provide a wrapper for Mail.php
 */

require_once 'globals.inc';
require_once 'Mail.php';

/** send email using from address stored in database settings table
 *
 * @param string $to          to address of message
 * @param string $subject     subject of message
 * @param string $message     message
 * @param array  $attachments file attachement
 *
 * @return boolean
 */
function send_email($to, $subject, $message, $attachments = array())
{
    //$smtpserver = split(":", setting('smtpserver','mailhub.iastate.edu'));
    //$smtpserver = split(":", setting('smtpserver','graingenes.org'));
    //$smtpserver = split(":", setting('smtpserver', 'mail.authsmtp.com'));

    $smtphost = $smtpserver[0];
    $smtpport = isset($smtpserver[1]) ? $smtpserver[1] : '25';
    ini_set('smtp', $smtpserver[1]);
    ini_set('smtp_port', $smtpport);
    $from = setting('mailfrom', 'noreply@graingenes.org');
    ini_set('sendmail_from', $from);

    $headers = array("From" => $from,
    "To" => $to,
    "Subject" => $subject,
    "X-Mailer" => "PHP/" . phpversion());
    if (count($attachments) > 0) {
        $boundary1 = rand(0, 9)."-"
        .rand(10000000000, 9999999999)."-"
        .rand(10000000000, 9999999999)."=:"
        .rand(10000, 99999);
        $boundary2 = rand(0, 9)."-".rand(10000000000, 9999999999)."-"
        .rand(10000000000, 9999999999)."=:"
        .rand(10000, 99999);
        $headers['Mime-Version'] = '1.0';
        $headers['Content-type'] = "multipart/mixed; boundary=\"$boundary1\"";
        $attBody = '';
        foreach ($attachments as $filename => $data) {
            $boundary1 = $boundary1;
            $cType = $data['content-type'];
            $text = $data['text'];
            $attBody = $attBody .
            "--" . $boundary1 . "\r\n" .
            "Content-type: $cType; name=\"$filename\"\r\n" .
            "Content-Transfer-Encoding: base64\r\n" .
            "Content-Disposition: attachment; filename=\"$filename\"" . "\r\n\r\n" .
            chunk_split(base64_encode($text)) . "\r\n";
        }
        $message = "\r\n" .
        "This is a multi-part message in MIME format." . "\r\n\r\n" .
        "--" . $boundary1 . "\r\n" .
        "Content-type: multipart/alternative; boundary=\"$boundary2\"" . "\r\n\r\n" .
        "--" . $boundary2 . "\r\n" .
        "Content-type: text/plain; charset=utf-8" . "\r\n" .
        "Content-Transfer-Encoding: 7bit" . "\r\n" . "\r\n" .

        $message . "\r\n" .
        "--" . $boundary2 . "--" . "\r\n" . "\r\n"
        . $attBody . "--" . $boundary1 . "--" . "\r\n\r\n";
    }
    if (setting('syslogmail', 0)) {
        openlog('mail', LOG_PID, LOG_MAIL);
        syslog(LOG_INFO, "to: $to\nemail:\n$message");
        closelog();
    }
    $mobj = &Mail::factory(
        setting('mailbackend', 'sendmail'),
        array('host' => $smtpserver, 'port' => $smtpport));
    if (PEAR::isError($mobj)) {
        echo $mobj->getMessage() . "\n" . $mobj->getUserInfo() . "\n";
        die();
    } else {
        return $mobj->send(array($to), $headers, $message);
    }
}

/** Same as above except specifying the From: address as a parameter.
 *
 * @param string $to          to address of message
 * @param string $subject     subject of message
 * @param string $from        from address of message
 * @param string $message     message
 * @param array  $attachments file attachement
 *
 * @return boolean
 *
 */
function send_email_from($to, $subject, $from, $message, $attachments = array())
{
    //$smtpserver = split(":", setting('smtpserver','mailhub.iastate.edu'));
    //$smtpserver = split(":", setting('smtpserver','graingenes.org'));
    //$smtpserver = split(":", setting('smtpserver', 'mail.authsmtp.com'));

    $smtphost = $smtpserver[0];
    $smtpport = isset($smtpserver[1]) ? $smtpserver[1] : '25';
    ini_set('smtp', $smtpserver[1]);
    ini_set('smtp_port', $smtpport);
    //$from = setting('mailfrom', 'noreply@tht.vrac.iastate.edu');
    ini_set('sendmail_from', $from);

  $headers = array("From" => $from,
		   "To" => $to,
		   "Subject" => $subject,
		   "X-Mailer" => "PHP/" . phpversion());
  if (count($attachments) > 0) {
    $boundary1 = rand(0,9)."-"
      .rand(10000000000,9999999999)."-"
      .rand(10000000000,9999999999)."=:"
      .rand(10000,99999);
    $boundary2 = rand(0,9)."-".rand(10000000000,9999999999)."-"
      .rand(10000000000,9999999999)."=:"
      .rand(10000,99999);
    $headers['Mime-Version'] = '1.0';
    $headers['Content-type'] = "multipart/mixed; boundary=\"$boundary1\"";
   $attBody = '';
   foreach ($attachments as $filename => $data) {
     $boundary1 = $boundary1;
     $cType = $data['content-type'];
     $text = $data['text'];
     $attBody = $attBody .
       "--" . $boundary1 . "\r\n" .
       "Content-type: $cType; name=\"$filename\"\r\n" .
       "Content-Transfer-Encoding: base64\r\n" .
       "Content-Disposition: attachment; filename=\"$filename\"" . "\r\n\r\n" .
       chunk_split(base64_encode($text)) . "\r\n";
   }
     $message = "\r\n" .
"This is a multi-part message in MIME format." . "\r\n\r\n" .
"--" . $boundary1 . "\r\n" .
"Content-type: multipart/alternative; boundary=\"$boundary2\"" . "\r\n\r\n" .
"--" . $boundary2 . "\r\n" .
"Content-type: text/plain; charset=utf-8" . "\r\n" .
"Content-Transfer-Encoding: 7bit" . "\r\n" . "\r\n" .

$message . "\r\n" .
"--" . $boundary2 . "--" . "\r\n" . "\r\n"
    . $attBody . "--" . $boundary1 . "--" . "\r\n\r\n";
  }
  if (setting('syslogmail', 0)) {
    openlog('mail', LOG_PID, LOG_MAIL);
    syslog(LOG_INFO, "to: $to\nemail:\n$message");
    closelog();
  }
  $mobj = &Mail::factory(setting('mailbackend', 'sendmail'),
			 array('host' => $smtpserver,
                               'port' => $smtpport));
  return $mobj->send(array($to), $headers, $message);
  }
