<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 21.11.18
 * @Time   : 19:12
 */


require_once __DIR__ . '/../inc/ajax_secure.php';

require_once __DIR__ . '/../inc/config.php';
$config = new config();

require_once __DIR__ . '/../inc/mysqli.php';

$vystup = array();

$vystup['code'] = 1;
$vystup['message'] = '';


$cmd = $_GET['cmd'] ?? $_POST['cmd'] ?? '';

switch ($cmd){
#region login
  case 'login':
    $login = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    //$password = md5($password);

    $stmt = $mysqli->prepare('SELECT id FROM user WHERE (name=? or email=?) and password=?');
    $stmt->bind_param('sss', $login, $login, $password);
    $stmt->bind_result($userId);
    $stmt->execute();
    $stmt->fetch();
    $stmt->close();

    if ($userId !== null){
      $vystup['code'] = 0;
      $_SESSION['user_id'] = $userId;
    } else {
      $vystup['code'] = 1;
      $vystup['message'] = 'Login failed';
    }

    break;
#endregion

#region register
  case 'register':

    break;
#endregion

#region lostPassword
  case 'lostPassword':

    break;
#endregion
}

header('Content-Type: application/json');
echo json_encode($vystup);
