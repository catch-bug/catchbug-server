<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 21.11.18
 * @Time   : 19:12
 */


use rollbug\config;
use rollbug\mailer;

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

#region delete_token
  case 'delete_token':
    $userId = (integer) ($_GET['userid'] ?? 0);
    $tokenId = (integer) ($_GET['tokenid'] ?? 0);

    if ($_SESSION['user_id'] === $userId) {
      $stmt = $mysqli->prepare('DELETE FROM token WHERE id=?');
      $stmt->bind_param('i', $tokenId);
      $query_success = $stmt->execute();
      $stmt->close();

      if($query_success) {
        $vystup['code'] = 0;
        $vystup['message'] = 'Token successfully deleted.';
      } else {
        $vystup['code'] = 1;
        $vystup['message'] = 'Delete token failed.';
      }
    } else {
      $vystup['code'] = 1;
      $vystup['message'] = 'Unauthorised access!';
    }
    break;
#endregion

#region delete_project
  case 'delete_project':
    $userId = (integer) ($_GET['userid'] ?? 0);
    $projectId = (integer) ($_GET['projectid'] ?? 0);
    if ($_SESSION['user_id'] === $userId) {
      $stmt = $mysqli->prepare('DELETE FROM project WHERE user_id=? and id=?');
      $stmt->bind_param('ii', $userId, $projectId);
      $query_success = $stmt->execute();
      $stmt->close();

      if($query_success) {
        $vystup['code'] = 0;
        $vystup['message'] = 'Project successfully deleted.';
      } else {
        $vystup['code'] = 1;
        $vystup['message'] = 'Delete project failed.';
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
      switch ($section) {
        #region general project settings
        case 'general': // update / create project
          $name = trim($_POST['name'] ?? '');
          $desc = trim($_POST['desc'] ?? '');
          if ($name === '') {
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
              $stmt = $mysqli->prepare('SELECT last_project+1 FROM `user` WHERE id=?');
              $stmt->bind_param('i', $userId);
              $stmt->bind_result($projectId);
              $query_success = $stmt->execute();
              $stmt->fetch();
              $stmt->close();

              $stmt = $mysqli->prepare('UPDATE `user` SET last_project=last_project+1 WHERE id=?');
              $stmt->bind_param('i', $userId);
              $query_success = $query_success && $stmt->execute();
              $stmt->close();

              $stmt = $mysqli->prepare('INSERT INTO project (id, user_id, name, description) VALUES (?,?,?,?);');
              $stmt->bind_param('iiss', $projectId, $userId, $name, $desc);
              $query_success = $query_success && $stmt->execute();
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
          break;  // END update / create project
        #endregion general project settings

        #region members project settings
        case 'members':
          $vystup['code'] = 1;
          $vystup['message'] = 'members.';
          break;
        #endregion members project settings

        #region tokens project settings
        case 'tokens':  // update / create token
          $tokeId = (integer)($_POST['tokenid'] ?? 0);
          $name = trim($_POST['name'] ?? '');
          $limitPer = $_POST['limit_per'] ?? 'Default';
          $limitCalls = (integer)($_POST['limit_calls'] ?? 0);
          $disabled = ($_POST['disabled'] ?? false) !== false;
          $type = implode(',', $_POST['type'] ?? []);

          $maxRate = $config->default_token_rate_limit;
          $rate = $maxRate;
          switch ($limitPer) {
            case '5 minutes':
              $rate = 5 * $maxRate;
              break;
            case '30 minutes':
              $rate = 30 * $maxRate;
              break;
            case '1 hour':
              $rate = 60 * $maxRate;
              break;
            case '1 day':
              $rate = 1440 * $maxRate;
              break;
            case '1 week':
              $rate = 10080 * $maxRate;
              break;
            case '1 month':
              $rate = 43200 * $maxRate;
              break;
          }

          if (($limitPer === 'Default') || ($limitCalls <= $rate)){
            $query_success = true;
            if ($tokeId > 0){ // edit
              $stmt = $mysqli->prepare('UPDATE token SET type=?, name=?, disabled=?, rate_limit_per=?, rate_limit_calls=? WHERE id=?;');
              $stmt->bind_param('ssisis', $type, $name, $disabled, $limitPer, $limitCalls, $tokeId);
            } else { // new toke
              try {
                $token = generateToken($mysqli);
              } catch (Exception $e) {
                $query_success = false;
                $error = $e->getMessage();
              }
              $stmt = $mysqli->prepare('INSERT INTO token (user_id, project_id, token, type, name, disabled, rate_limit_per, rate_limit_calls) VALUES (?,?,?,?,?,?,?,?);');
              $stmt->bind_param('iisssisi', $userId, $projectId, $token, $type, $name, $disabled, $limitPer, $limitCalls);
            }
            $query_success = $query_success && $stmt->execute();
            $error = $stmt->error;

            if($query_success) {
              $vystup['code'] = 0;
              $vystup['message'] = 'Token successfully saved.';
            } else {
              $vystup['code'] = 1;
              $vystup['message'] = 'Saving token failed. ' . $error;
            }

          } else {
            $vystup['code'] = 1;
            $vystup['message'] = 'You can set max rate ' . $rate . 'not ' . $limitCalls . '.';
          }
          break;  // END update / create token
        #endregion tokens project settings
      }



    } else {
      $vystup['code'] = 1;
      $vystup['message'] = 'Unauthorised access!';
    }
    break;
#endregion project_settings


#region user_settings
  case 'user_settings':
    $userId = (integer) ($_POST['user_id'] ?? 0);

    if ($_SESSION['user_id'] === $userId) {
      $section = trim($_POST['section'] ?? '');

      $error = '';
      switch ($section) {

        // change password
        #region change_password
        case 'auth_ch_pwd':
          $oldPassword = trim($_POST['old_password'] ?? '');

          $stmt = $mysqli->prepare('SELECT password FROM user WHERE id=? LIMIT 1');
          $stmt->bind_param('i', $userId);
          $stmt->bind_result($password);
          $query_success = $stmt->execute();
          $error = $stmt->error;
          $stmt->fetch();
          $stmt->close();

          if (password_verify($oldPassword, $password)) {
            $newPassword = trim($_POST['new_password'] ?? '');
            $newPasswordConfirm = trim($_POST['confirm_new_password'] ?? '');

            if ($query_success && ($newPassword === $newPasswordConfirm)){
              $passwordHast = password_hash($newPassword, PASSWORD_DEFAULT);

              $stmt = $mysqli->prepare('UPDATE user SET password=? WHERE id=?;');
              $stmt->bind_param('si', $passwordHast, $userId);
              $query_success = $query_success && $stmt->execute();
              $error = $stmt->error;
              $stmt->close();

              if ($query_success){
                $vystup['code'] = 0;
                $vystup['message'] = 'Password successfully updated.';
              } else {
                $vystup['code'] = 1;
                $vystup['message'] = 'Database error: ' . $error;
              }
            } else {
              $vystup['code'] = 1;
              $vystup['message'] = 'Passwords not match.';
            }
          } else {
            $vystup['code'] = 1;
            $vystup['message'] = 'Wrong old password';
          }
          break;
          #endregion change_password

        #region verify_email
        case 'verify_email':
          $email = trim($_POST['email'] ?? '');
          $id = (integer)trim($_POST['id'] ?? 0);

          $stmt = $mysqli->prepare('SELECT email, user_id FROM user_emails WHERE id=? LIMIT 1');
          $stmt->bind_param('i', $id);
          $stmt->bind_result($dbEmail, $dbUserId);
          $query_success = $stmt->execute();
          $stmt->fetch();
          $stmt->close();

          if($query_success && ($dbEmail === $email) && ($dbUserId === $_SESSION['user_id'])){
            try {
              $hash = bin2hex(random_bytes(16));
            } catch (Exception $e) {
              $vystup['code'] = 1;
              $vystup['message'] = 'Unable to generate hash. Error: ' . $e->getMessage();
            }
            $stmt = $mysqli->prepare('UPDATE user_emails SET hash=? WHERE id=?;');
            $stmt->bind_param('si', $hash, $id);
            $query_success = $stmt->execute();
            $stmt->close();

            if ($query_success){

              try {
                $mail = new mailer($config);
                $mail->addAddress($email);
                $mail->Subject = 'rollBug server email confirmation.';
                $base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
                $confirmUrl = "$base_url/confirm_email.php?email=" . urlencode($email) . "&hash=$hash";
                $mail->setBody("<h1>rollBug server email confirmation</h1>You recently entered a new contact email address into rollBug.<br>To confirm your contact email, follow the link below:<br><a href='$confirmUrl'>Confirm this email address</a><br>The rollBug server");

                if ($mail->send()) {
                  $vystup['code'] = 0;
                  $vystup['message'] = 'Email with confirmation lik was sent to ' . $email;
                } else {
                  $vystup['code'] = 1;
                  $vystup['message'] = 'Error sending message.';
                }
              } catch (\PHPMailer\PHPMailer\Exception $e) {
                $vystup['code'] = 500;
                $vystup['message'] = 'Error sending message.';
              }
            } else {
              $vystup['code'] = 1;
              $vystup['message'] = 'Unable write hash to database.';
            }
          } else {
            $vystup['code'] = 1;
            $vystup['message'] = 'Unauthorised email verification.';
          }
          break;
          #endregion verify_email

        #region delete_email
        case 'delete_email':
          $email = trim($_POST['email'] ?? '');
          $id = (integer)trim($_POST['id'] ?? 0);

          $stmt = $mysqli->prepare('SELECT email, user_id, main FROM user_emails WHERE id=? LIMIT 1');
          $stmt->bind_param('i', $id);
          $stmt->bind_result($dbEmail, $dbUserId, $mainEmail);
          $query_success = $stmt->execute();
          $stmt->fetch();
          $stmt->close();

          $mysqli->autocommit(false);
          if($query_success && ($dbEmail === $email) && ($dbUserId === $_SESSION['user_id'])){
            $stmt = $mysqli->prepare('DELETE FROM user_emails WHERE id=?');
            $stmt->bind_param('i', $id);
            $query_success = $stmt->execute();
            $stmt->close();

            if ($mainEmail){
              $stmt = $mysqli->prepare('UPDATE user_emails SET main = 1 WHERE user_id=? limit 1;');
              $stmt->bind_param('i', $userId);
              $query_success = $query_success && $stmt->execute();
              $stmt->close();
            }

            if ($query_success) {
              if ($mysqli->commit()) {
                $vystup['code'] = 0;
                $vystup['message'] = 'Email ' . $email . ' successfully deleted.';
                $vystup['forceReload'] = true;
              } else {
                $vystup['code'] = 1;
                $vystup['message'] = 'Database error when deleting email.';
              }
            } else {
              $vystup['code'] = 1;
              $vystup['message'] = 'Database error when deleting email.';
            }

          } else {
            $vystup['code'] = 1;
            $vystup['message'] = 'Unauthorised email delete.';
          }
          break;
          #endregion delete_email

        #region add_email
        case 'add_email':
          $email = trim($_POST['email'] ?? '');

          $stmt = $mysqli->prepare('SELECT count(id) FROM user_emails WHERE user_id=? and email=?');
          $stmt->bind_param('is', $userId, $email);
          $stmt->bind_result($count);
          $stmt->execute();
          $stmt->fetch();
          $stmt->close();

          if ($count === 0) {

            $stmt = $mysqli->prepare('INSERT INTO user_emails (user_id, email, main) select ?,?,count(id)=0 from user_emails where user_id=?;');
            $stmt->bind_param('isi', $userId, $email, $userId);
            $query_success = $stmt->execute();
            $stmt->close();

            if ($query_success) {
              $vystup['code'] = 0;
              $vystup['message'] = 'Email ' . $email . ' successfully added.';
              $vystup['forceReload'] = true;
            } else {
              $vystup['code'] = 1;
              $vystup['message'] = 'Database error when adding email.';
            }
          } else {
            $vystup['code'] = 1;
            $vystup['message'] = 'Email ' . $email . ' already added.';
          }

          break;
          #endregion add_email

        #region set_main_email
        case 'set_main_email':
          $mainEmail = (integer)trim($_POST['main_email'] ?? 0);

          $stmt = $mysqli->prepare('SELECT email, user_id FROM user_emails WHERE id=? LIMIT 1');
          $stmt->bind_param('i', $mainEmail);
          $stmt->bind_result($dbEmail, $dbUserId);
          $query_success = $stmt->execute();
          $stmt->fetch();
          $stmt->close();

          if($query_success && ($dbUserId === $_SESSION['user_id'])){
            $mysqli->autocommit(false);

            $stmt = $mysqli->prepare('UPDATE user_emails SET main=0 WHERE user_id=?;');
            $stmt->bind_param('i', $userId);
            $query_success = $stmt->execute();
            $stmt->close();

            $stmt = $mysqli->prepare('UPDATE user_emails SET main=1 WHERE user_id=? and id=?;');
            $stmt->bind_param('ii', $userId, $mainEmail);
            $query_success = $query_success && $stmt->execute();
            $stmt->close();

            if ($query_success) {
              if ($mysqli->commit()) {
                $vystup['code'] = 0;
                $vystup['message'] = 'Email ' . $dbEmail . ' set as main.';
                $vystup['forceReload'] = true;
              } else {
                $vystup['code'] = 1;
                $vystup['message'] = 'Database error when setting main email.';
              }
            } else {
              $vystup['code'] = 1;
              $vystup['message'] = 'Database error when setting main email.';
            }
          } else {
            $vystup['code'] = 1;
            $vystup['message'] = 'Unauthorised email settings.';
          }
          break;
          #endregion set_main_email

        #region delete_user
        case 'delete_user':
          $userName = trim($_POST['user_name'] ?? '');

          $stmt = $mysqli->prepare('SELECT name, root FROM user WHERE id=?');
          $stmt->bind_param('i', $userId);
          $stmt->bind_result($dbUserName, $dbUserRoot);
          $stmt->execute();
          $stmt->fetch();
          $stmt->close();

          if ($dbUserName === $userName){
            if ($dbUserRoot){
              $stmt = $mysqli->prepare('SELECT count(id) FROM user WHERE root!=0');
              $stmt->bind_result($count);
              $stmt->execute();
              $stmt->fetch();
              $stmt->close();

              $accountDeletable = $count > 1;
            } else {
              $accountDeletable = true;
            }

            if ($accountDeletable){
              $stmt = $mysqli->prepare('DELETE FROM user WHERE id=?');
              $stmt->bind_param('i', $userId);
              $query_success = $stmt->execute();
              $stmt->close();

              if ($query_success){
                $vystup['code'] = 0;
                $vystup['message'] = 'User ' . $dbUserName . ' successfully deleted.';
                $vystup['replace'] = '/';
                // logout deleted user
                $_SESSION['user_id'] = '';
                unset ($_SESSION['user_id']);

                $_COOKIE['auth'] = '';
                setcookie ('auth', '', time() - 3600,'/');
              } else {
                $vystup['code'] = 1;
                $vystup['message'] = 'Database error when deleting user.';
              }

            } else {
              $vystup['code'] = 1;
              $vystup['message'] = 'You can\'t delete last root user.';
            }
          } else {
            $vystup['code'] = 1;
            $vystup['message'] = 'Unauthorised user delete.';
          }
          break;
        #endregion delete_user
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
