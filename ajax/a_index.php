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

      $error = '';
      switch ($section){
        case 'general': // update / create project
          $name = trim($_POST['name'] ?? '');
          $desc = trim($_POST['desc'] ?? '');
          if ($name === ''){
            $vystup['code'] = 1;
            $vystup['message'] = 'Error in project name';
          } else {
            if ($projectId > 0) {  // update project
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
            } else {  // create project
              $mysqli->autocommit(false);
              $stmt = $mysqli->prepare('SELECT last_project+1 FROM user WHERE id=?');
              $stmt->bind_param('i', $userId);
              $stmt->bind_result($projectId);
              $stmt->execute();
              $stmt->fetch();
              $stmt->close();

              $stmt = $mysqli->prepare('INSERT INTO project (id, user_id, name, description) VALUES (?,?,?,?);');
              $stmt->bind_param('iiss', $projectId, $userId, $name, $desc);
              $query_success = $stmt->execute();
              $error = $stmt->error;
              $stmt->close();

              $token = '';
              $type = '';
              $stmt = $mysqli->prepare('INSERT INTO token (user_id, project_id, token, type) VALUES (?,?,?,?);');
              $stmt->bind_param('iiss', $userId, $projectId, $token, $type);

              try {
                $token = generateToken($mysqli);
                $type = 'post_client_item';
                $query_success = $query_success && $stmt->execute();
                $error = $stmt->error;

                $token = generateToken($mysqli);
                $type = 'post_server_item';
                $query_success = $query_success && $stmt->execute();
                $error = $stmt->error;

              } catch (Exception $e) {
                $query_success = false;
                $error = $e->getMessage();
              }

              if ($query_success) {
                if ($mysqli->commit()) {
                  $vystup['code'] = 0;
                  $vystup['forceReload'] = false;
                  $vystup['replace'] = "{$config->rewrite}project/$projectId/settings/tokens";
                  $vystup['message'] = 'Project successfully created.';
                } else {
                  $vystup['code'] = 1;
                  $vystup['message'] = 'Creating project failed.';
                }
              } else {
                $mysqli->rollback();
                $vystup['code'] = 1;
                $vystup['message'] = 'Creating project failed.' . $error;
              }
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


/**
 * @param \mysqli $mysqli
 * @param int     $length
 *
 * @return string
 * @throws \Exception
 */
function generateToken (\mysqli $mysqli, int $length = 32): string
{
  $bytes = (int) $length/2;
  $token = '';
  $count = 1;

  $stmt =$mysqli->prepare('SELECT count(id) FROM token WHERE token=?');
  $stmt->bind_param('s', $token);
  $stmt->bind_result($count);

  while ($count > 0) {
    $token = bin2hex(random_bytes($bytes));
    $stmt->execute();
    $stmt->fetch();
  }
  $stmt->close();

  return $token;
}
