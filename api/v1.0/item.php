<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 7.11.18
 * @Time   : 20:28
 */

ob_start();
require_once __DIR__ . '/../../inc/mysqli.php';



switch ($method){
  case 'POST':

    $uuid = $payload->data->uuid;


    $stmt = $mysqli->prepare('select user.id, p.last_item, p.id, t.type from user join project p on user.id = p.user_id join token t on p.user_id = t.user_id and p.id = t.project_id where t.token=? and FIND_IN_SET(\'post_server_item\',t.type)>0');
    $stmt->bind_param('s', $access_token);
    $stmt->bind_result($userId, $last_item, $projectId, $tokenType);
    $stmt->execute();
    $mysqli_error .= "\n" . $mysqli->error;
    $stmt->fetch();
    $stmt->close();

    if ($userId !== null) {

      $query_success = true;

      $mysqli->autocommit(false);
      $mysqli_error = '';

      switch ($payload->data->language) {
        case 'php':
          $id_str = $payload->data->body->trace->frames[0]->filename . '|'
              . $payload->data->body->trace->frames[0]->lineno . '|'
              . $payload->data->body->trace->exception->class . '|'
              . $payload->data->body->trace->exception->message;
          break;

      }

      // search for item in db
      $stmt = $mysqli->prepare('select item.id, item.last_occ from item where user_id=? and project_id=? and id_str=?');
      $stmt->bind_param('iis', $userId, $projectId, $id_str);
      $stmt->bind_result($itemId, $last_occ);
      $query_success = $query_success && $stmt->execute();
      $mysqli_error .= "\n" . $mysqli->error;
      $stmt->fetch();
      $stmt->close();

      $date = new DateTime('@' . $payload->data->timestamp);
      $timestamp = $date->format('Y-m-d H:i:s');

      // if new item
      if ($itemId === null) {
        $itemId = $last_item + 1;
        $stmt = $mysqli->prepare('INSERT INTO item (id, user_id, project_id, level, language, id_str, last_occ, last_timestamp) VALUES (?,?,?,?,?,?,1, ?);');
        $stmt->bind_param('iiissss', $itemId, $userId, $projectId, $payload->data->level, $payload->data->language, $id_str, $timestamp);
        $query_success = $query_success && $stmt->execute();
        $mysqli_error .= "\n" . $mysqli->error;
        $stmt->fetch();
        $stmt->close();

        $stmt = $mysqli->prepare('UPDATE project SET last_item = last_item + 1 WHERE user_id=? and id=?');
        $stmt->bind_param('ii', $userId, $projectId);
        $query_success = $query_success && $stmt->execute();
        $mysqli_error .= "\n" . $mysqli->error;
        $stmt->close();

        $last_occ = 1;
      } else {
        $stmt = $mysqli->prepare('UPDATE item SET last_occ = last_occ + 1, last_timestamp=? WHERE user_id=? and project_id=? and id=?');
        $stmt->bind_param('siii', $timestamp, $userId, $projectId, $itemId);
        $query_success = $query_success && $stmt->execute();
        $mysqli_error .= "\n" . $mysqli->error;
        $stmt->close();

        $last_occ++;
      }

      if ($last_occ <= $config->max_occurences) {
        $data = json_encode($payload->data);
        $stmt = $mysqli->prepare('INSERT INTO occurence (id, user_id, project_id, item_id, timestamp, data) VALUES (?,?,?,?,?,?);');
        $stmt->bind_param('iiiiss', $last_occ, $userId, $projectId, $itemId, $timestamp, $data);
        $query_success = $query_success && $stmt->execute();
        $mysqli_error .= "\n" . $mysqli->error;
        $stmt->fetch();
        $stmt->close();
      }

      /*
      $fp = fopen(__DIR__ . "/mysqli.txt", 'wb');
      fwrite($fp, $mysqli_error);
      fclose($fp);
       */

      if ($query_success) {
        if ($mysqli->commit()) {
          header('Content-Type: application/json; charset=utf-8');
          http_response_code(200);
          echo "{err: 0,result: {uuid: \"$uuid\"}}";
        } else {
          header('Content-Type: application/json; charset=utf-8');
          http_response_code(500);
          echo '{"err": 1, "message": "DB error"}';
        }
      } else {
        $mysqli->rollback();
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo '{"err": 1, "message": "DB error"}';
      }

    } else {
      header('Content-Type: application/json; charset=utf-8');
      http_response_code(500);
      echo '{"err": 1, "message": "wrong token"}';
    }

    break;

  case 'GET':
    break;

  case 'PATCH':
    break;
}


$content = ob_get_contents();
$length = strlen($content);
header('Content-Length: '. $length);
echo $content;
