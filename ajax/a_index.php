<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 21.11.18
 * @Time   : 19:12
 */


use rollbug\config;

require_once __DIR__ . '/../inc/ajax_secure.php';
require_once __DIR__ . '/../vendor/autoload.php';

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

#region delete_item
  case 'delete_item':
    $userId = (integer) ($_GET['userid'] ?? 0);
    $projectId = (integer) ($_GET['projectid'] ?? 0);
    $itemId = (integer) ($_GET['itemid'] ?? 0);

    if ($_SESSION['user_id'] === $userId) {
      $stmt = $mysqli->prepare('DELETE FROM item WHERE user_id=? and project_id=? and id=?');
      $stmt->bind_param('iii', $userId, $projectId, $itemId);
      $query_success = $stmt->execute();
      $stmt->close();

      if($query_success) {
        $vystup['code'] = 0;
        $vystup['message'] = 'Item successfully deleted.';
      } else {
        $vystup['code'] = 1;
        $vystup['message'] = 'Delete item failed.';
      }
    } else {
      $vystup['code'] = 1;
      $vystup['message'] = 'Unauthorised access!';
    }
    break;
#endregion
}

header('Content-Type: application/json');
echo json_encode($vystup);
