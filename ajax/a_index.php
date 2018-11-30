<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 21.11.18
 * @Time   : 19:12
 */


use Composer\Plugin\PreCommandRunEvent;
use rollbug\config;

require_once __DIR__ . '/../inc/ajax_secure.php';
require_once __DIR__ . '/../vendor/autoload.php';

$config = new config();

require_once __DIR__ . '/../inc/mysqli.php';

$vystup = array();

$vystup['code'] = 1;
$vystup['message'] = 'Command not found.';


$cmd = $_GET['cmd'] ?? $_POST['cmd'] ?? '';

switch ($cmd){
#region login
  case 'login':
    $login = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    //$password = md5($password);

    $stmt = $mysqli->prepare('SELECT id, password FROM user WHERE name=?');
    $stmt->bind_param('s', $login);
    $stmt->bind_result($userId, $userPassword);
    $stmt->execute();
    $stmt->fetch();
    $stmt->close();

    if (password_verify($password, $userPassword)){
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
    $userName = trim($_POST['username'] ?? '');
    $userPassword = trim ($_POST['password'] ?? '');
    $userPasswordConfirm = trim ($_POST['passwordConfirm'] ?? '');

    if ($userPassword !== $userPasswordConfirm){
      $vystup['code'] = 1;
      $vystup['message'] = 'Passwords not matches.';
    } else {
      $stmt = $mysqli->prepare('SELECT count(id) FROM user WHERE name=? LIMIT 1');
      $stmt->bind_param('s', $userName);
      $stmt->bind_result($pocet);
      $stmt->execute();
      $stmt->fetch();
      $stmt->close();

      if ($pocet > 0){
        $vystup['code'] = 1;
        $vystup['message'] = 'User with name ' . $userName . ' already registered. Try another name.';
      } else {
        $userPassword = password_hash($userPassword, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare('INSERT INTO user (name, password) VALUES (?,?);');
        $stmt->bind_param('ss', $userName, $userPassword);
        $query_success = $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();

        if ($query_success){
          $_SESSION['user_id'] = $userId;
          $vystup['code'] = 0;
          $vystup['message'] = 'You successfully registered.';
        } else {
          $vystup['code'] = 1;
          $vystup['message'] = 'Database error.';
        }

      }
    }

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

#region delete_occurrence
  case 'delete_occurrence':
    $userId = (integer) ($_GET['userid'] ?? 0);
    $projectId = (integer) ($_GET['projectid'] ?? 0);
    $itemId = (integer) ($_GET['itemid'] ?? 0);
    $occurrenceId = (integer) ($_GET['occurrenceid'] ?? 0);

    if ($_SESSION['user_id'] === $userId) {
      $stmt = $mysqli->prepare('DELETE FROM occurrence WHERE user_id=? and project_id=? and item_id=? and id=?');
      $stmt->bind_param('iiii', $userId, $projectId, $itemId, $occurrenceId);
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

#region change_level
  case 'change_level':
    $userId = (integer) ($_GET['userid'] ?? 0);
    $projectId = (integer) ($_GET['projectid'] ?? 0);
    $itemId = (integer) ($_GET['itemid'] ?? 0);
    $level = trim($_GET['level'] ?? '');

    if ($_SESSION['user_id'] === $userId) {
      $stmt = $mysqli->prepare('UPDATE item SET level = ? WHERE user_id=? and project_id=? and id=?');
      $stmt->bind_param('siii', $level, $userId, $projectId, $itemId);
      $query_success = $stmt->execute();
      $stmt->close();

      if($query_success) {
        $vystup['code'] = 0;
        $vystup['message'] = 'Item level successfully changed.';
      } else {
        $vystup['code'] = 1;
        $vystup['message'] = 'Updating item level failed.';
      }
    } else {
      $vystup['code'] = 1;
      $vystup['message'] = 'Unauthorised access!';
    }
    break;
#endregion

#region project_settings
  case 'project_settings':
    $userId = (integer) ($_POST['userid'] ?? 0);

    if ($_SESSION['user_id'] === $userId) {
      $projectId = (integer) ($_POST['projectid'] ?? 0);
      $section = trim($_POST['section'] ?? '');

      switch ($section){
        case 'general':
          $name = trim($_POST['name'] ?? '');
          $desc = trim($_POST['desc'] ?? '');
          if ($name === ''){
            $vystup['code'] = 1;
            $vystup['message'] = 'Error in project name';
          } else {
            $stmt = $mysqli->prepare('UPDATE project SET name=?, description=? WHERE user_id=? and id=?;');
            $stmt->bind_param('ssii', $name, $desc, $userId, $projectId);
            $query_success = $stmt->execute();
            $stmt->close();

            if ($query_success) {
              $vystup['code'] = 0;
              $vystup['forceReload'] = true;
              $vystup['message'] = 'Project successfully updated.';
            } else {
              $vystup['code'] = 1;
              $vystup['message'] = 'Updating project failed.';
            }
          }
          break;

        case 'members':

          break;

        case 'tokens':

          break;
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
