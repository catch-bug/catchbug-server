<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 12.12.18
 * @Time   : 19:43
 */


use rollbug\config;
use rollbug\mailer;

require_once __DIR__ . '/../inc/ajax_secure.php';
require_once __DIR__ . '/../vendor/autoload.php';

$vystup = array();
$vystup['code'] = 1;
$vystup['message'] = 'Command not found.';

try {
  $config = new config();
} catch (\Exception $e) {
  $vystup['code'] = 500;
  $vystup['message'] = $e->getMessage();

  header('Content-Type: application/json');
  die(json_encode($vystup));
}

require_once __DIR__ . '/../inc/mysqli.php';

$cmd = $_GET['cmd'] ?? $_POST['cmd'] ?? '';

switch ($cmd){
  #region system_settings
  case 'settings_general':
    $config->max_occurences = (integer) trim($_POST['config']['max_occurrences'] ?? 10);
    $config->default_token_rate_limit = (integer) trim($_POST['config']['default_token_rate_limit'] ?? 1000);
    $config->rewrite = ($_POST['config']['rewrite'] ?? 'off') === 'off' ? '/?' : '/';
    $config->account_reg = ($_POST['config']['account_reg'] ?? 'off') === 'on';
    $config->auto_update = ($_POST['config']['auto_update'] ?? 'off') === 'on';

    $smtpConfig = $_POST['config']['smtp'] ?? false;
    if ($smtpConfig !== false) {
      $config->smtp->smtp_enable = ($smtpConfig['smtp_enable'] ?? 'off') === 'on';
      $config->smtp->smtp_host = trim($smtpConfig['smtp_host'] ?? '');
      $config->smtp->smtp_port = trim($smtpConfig['smtp_port'] ?? 0);
      $config->smtp->smtp_user = trim($smtpConfig['smtp_user'] ?? '');
      $config->smtp->smtp_password = trim($smtpConfig['smtp_password'] ?? '');
      $config->smtp->smtp_secure = $smtpConfig['smtp_secure'];
      $config->smtp->smtp_from_addr = trim($smtpConfig['smtp_from_addr'] ?? '');
      $config->smtp->smtp_from_name = trim($smtpConfig['smtp_from_name'] ?? '');
      $config->smtp->smtp_html_enable = ($smtpConfig['smtp_html_enable'] ?? 'off') === 'on';
    }

    try {
      $config->saveData();
      $vystup['code'] = 0;
      $vystup['message'] = 'Configuration has been saved.';
      $vystup['replace'] = "{$config->rewrite}system/";
      header('Cache-Control: no-cache, must-revalidate');
      header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    } catch (Exception $e) {
      $vystup['code'] = 1;
      $vystup['message'] = $e->getMessage();
    }
    break;
    #endregion

  #region test_smtp_settings
  case 'test_smtp_settings':
    $smtpConfig = $_POST['config']['smtp'] ?? false;

    if ($smtpConfig !== false) {
      $errors = [];
      try {
        $mail = new mailer(null, true);
        //Debug output
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function ($str, $level) {
          global $errors;
          if (strpos($str, 'SMTP connect() failed.') !== false) {
            $str = 'SMTP connect() failed.';
          }
          $errors[] = $str . '<br>';
        };

        //Server settings
        $mail->Host = gethostbyname(trim($smtpConfig['smtp_host'] ?? ''));

        $smtpUser = trim($smtpConfig['smtp_user'] ?? '');
        $smtpPassword = trim($smtpConfig['smtp_password'] ?? '');

        if (($smtpConfig['smtp_enable'] ?? 'off') === 'on') {
          $mail->isSMTP();
          $mail->SMTPAuth = true;
          $mail->Username = $smtpUser;
          $mail->Password = $smtpPassword;
          $mail->SMTPSecure = $smtpConfig['smtp_secure'];
          $mail->Port = trim($smtpConfig['smtp_port'] ?? 0);
        } else {
          $mail->isMail();
        }

        //Recipients
        $mail->setFrom(trim($smtpConfig['smtp_from_addr'] ?? ''), trim($smtpConfig['smtp_from_name'] ?? ''));
        $mail->addAddress(trim($smtpConfig['test_mail'] ?? ''));

        //Content
        $mail->isHTML(($smtpConfig['smtp_html_enable'] ?? 'off') === 'on');
        $mail->Subject = 'Test message from rollBug';
        $mail->setBody('<h1>Test message</h1>This is the HTML message body <b>in bold!</b>');


        if ($mail->send()) {
          $vystup['code'] = 0;
          $vystup['message'] = 'Message has been sent';
        } else {
          $vystup['code'] = 1;
          $vystup['message'] = 'Error sending message.';
        }

      } catch (\Exception $e) {
        $vystup['code'] = 1;
        $vystup['message'] = $errors;
      }

    } else {
      $vystup['code'] = 1;
      $vystup['message'] = 'Error';
    }

    break;
    #endregion
}

header('Content-Type: application/json');
echo json_encode($vystup);
