<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 7.11.18
 * @Time   : 20:28
 */
use \rollbug\itemWriter;

ob_start();
require_once __DIR__ . '/../../inc/mysqli.php';

// SELECT COUNT(id) FROM `item` WHERE `project_id`=1 and `real_time`>(DATE(NOW()) - INTERVAL 20 MINUTE)

switch ($method){
  case 'POST':

    if ($payload !== null) {

      $stmt = $mysqli->prepare('select user.id, p.last_item, p.id, t.type from user join project p on user.id = p.user_id join token t on p.user_id = t.user_id and p.id = t.project_id where t.token=?');
      $stmt->bind_param('s', $access_token);
      $stmt->bind_result($userId, $last_item, $projectId, $tokenType);
      $stmt->execute();
    //  $mysqli_error .= "\n" . $mysqli->error;
      $stmt->fetch();
      $stmt->close();

      if ($userId !== null) {
        $mysqli->autocommit(false);

        $itemWriter = new itemWriter($mysqli, $config);

        // todo check /  switch server/client token


        if (property_exists($payload->data->body, 'trace_chain')) {
          $itemWriter->setItemType('trace');
          $itemWriter->setUserId($userId);
          $itemWriter->setProjectId($projectId);
          $itemWriter->setLastItem($last_item);

          $trace_chain = $payload->data->body->trace_chain;
          unset ($payload->data->body->trace_chain);
          foreach ($trace_chain as $trace){
            $id_str = $itemWriter->makeIdStr($trace);
            $payload->data->body->trace = $trace;
            $itemWriter->setPayload($payload);
            $itemWriter->setIdStr($id_str);

            $itemWriter->writeItem(true);
          }

        } elseif (property_exists($payload->data->body, 'trace')) {
          $itemWriter->setItemType('trace');
          $itemWriter->setUserId($userId);
          $itemWriter->setProjectId($projectId);
          $itemWriter->setPayload($payload);
          $itemWriter->setLastItem($last_item);

          $id_str = $itemWriter->makeIdStr($payload->data->body->trace);
          $itemWriter->setIdStr($id_str);

          $itemWriter->writeItem();

        } elseif (property_exists($payload->data->body, 'message')) {
          $itemWriter->setItemType('message');
          $id_str = ' | |' . $payload->data->level . '|' . $payload->data->body->message->body;
          $itemWriter->setIdStr($id_str);
          $itemWriter->setPayload($payload);
          $itemWriter->setUserId($userId);
          $itemWriter->setProjectId($projectId);
          $itemWriter->setLastItem($last_item);

          $itemWriter->writeItem();


        } elseif (property_exists($payload->data->body, 'crash_report')) {
          $itemWriter->setItemType('crash_report');

        }

        if ($itemWriter->isQuerySuccess()) {
          if ($mysqli->commit()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(200);
            $uuid = $payload->data->uuid;
            echo "{err: 0,result: {uuid: \"$uuid\"}}";
          } else {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo "{\"err\": 1, \"message\": \"DB error: {$itemWriter->getMysqliError()}\"}";
          }
        } else {
          $mysqli->rollback();
          header('Content-Type: application/json; charset=utf-8');
          http_response_code(500);
          echo "{\"err\": 1, \"message\": \"DB error: {$itemWriter->getMysqliError()}\"}";
        }


      } else {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo '{"err": 1, "message": "wrong token"}';
      }

    } else {
      header('Content-Type: application/json; charset=utf-8');
      http_response_code(500);
      echo '{"err": 1, "message": "missing payload data"}';
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



