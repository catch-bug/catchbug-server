<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 14.12.18
 * @Time   : 0:26
 */

use rollbug\config;

require_once __DIR__ . '/vendor/autoload.php';
try {
  $config = new config(true);
} catch (Exception $e) {
  die($e->getMessage());
}
require_once __DIR__ . '/inc/mysqli.php';

$email = trim($_GET['email'] ?? '');
$hash = trim($_GET['hash'] ?? '');

$stmt = $mysqli->prepare('SELECT id FROM user_emails WHERE email=? and hash=? LIMIT 1');
$stmt->bind_param('ss', $email, $hash);
$stmt->bind_result($emailId);
$query_success = $stmt->execute();
$stmt->fetch();
$stmt->close();

if ($query_success && ($emailId !== null)){
  $stmt = $mysqli->prepare('UPDATE user_emails SET verified=1 WHERE id=?;');
  $stmt->bind_param('i', $emailId);
  $query_success = $stmt->execute();
  $stmt->close();

  if ($query_success) {
    $message = 'Email ' . $email . ' has been verified.';
  } else {
    $message = 'Error verifying email.';
  }
} else {
  $message = 'Error verifying email.';
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
</head>
<body>
<h3 style="padding-top: 5rem; text-align: center"><?php echo $message ?></h3>

</body>
</html>


