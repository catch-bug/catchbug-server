<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 26.11.18
 * @Time   : 2:21
 */

namespace rollbug;

class itemWriter
{

  /**
   * @var \stdClass
   */
  private $payload;
  /**
   * @var int
   */
  private $last_item;
  /**
   * @var int
   */
  private $userId;
  /**
   * @var int
   */
  private $projectId;
  /**
   * @var int
   */
  private $tokenId;
  /**
   * @var string
   */
  private $id_str;
  /**
   * @var string
   */
  private $itemType;
  /**
   * @var int
   */
  private $firstInChain = 0;

  /**
   * @var \mysqli
   */
  private $mysqli;
  /**
   * @var config
   */
  private $config;

  /**
   * @var bool
   */
  private $query_success = true;
  /**
   * @var string
   */
  private $mysqli_error = '';

  /**
   * itemWriter constructor.
   *
   * @param \mysqli $mysqli
   * @param config $config
   */
  public function __construct(\mysqli $mysqli, config $config, int $tokenId)
  {
    $this->mysqli = $mysqli;
    $this->config = $config;
    $this->tokenId = $tokenId;
  }

  /**
   * @param \stdClass $payload
   */
  public function setPayload(\stdClass $payload): void
  {
    $this->payload = $payload;
  }

  /**
   * @param int $last_item
   */
  public function setLastItem(int $last_item): void
  {
    $this->last_item = $last_item;
  }

  /**
   * @param int $userId
   */
  public function setUserId(int $userId): void
  {
    $this->userId = $userId;
  }

  /**
   * @param int $projectId
   */
  public function setProjectId(int $projectId): void
  {
    $this->projectId = $projectId;
  }

  /**
   * @param string $id_str
   */
  public function setIdStr(string $id_str): void
  {
    $this->id_str = $id_str;
  }

  /**
   * @param string $itemType
   */
  public function setItemType(string $itemType): void
  {
    $this->itemType = $itemType;
  }


  /**
   * @param int $firstInChain
   */
  public function setFirstInChain(int $firstInChain): void
  {
    $this->firstInChain = $firstInChain;
  }

  /**
   * @return \stdClass
   */
  public function getPayload(): \stdClass
  {
    return $this->payload;
  }

  /**
   * @return int
   */
  public function getFirstInChain(): int
  {
    return $this->firstInChain;
  }

  /**
   * @return bool
   */
  public function isQuerySuccess(): bool
  {
    return $this->query_success;
  }

  /**
   * @return string
   */
  public function getMysqliError(): string
  {
    return $this->mysqli_error;
  }

  /**
   * @param \stdClass $trace
   *
   * @return string
   */
  public function makeIdStr(\stdClass $trace): string
  {
    return $trace->frames[ \count($trace->frames) - 1 ]->filename . '|'
        . $trace->frames[ \count($trace->frames) - 1 ]->lineno . '|'
        . $trace->exception->class . '|'
        . $trace->exception->message;
  }

  /**
   * @param bool $traceChain
   */
  public function writeItem(bool $traceChain = false): void
  {

    // search for item in db
    $stmt = $this->mysqli->prepare('select item.id, item.last_occ from item where user_id=? and project_id=? and id_str=?');
    $stmt->bind_param('iis', $this->userId, $this->projectId, $this->id_str);
    $stmt->bind_result($itemId, $last_occ);
    $this->query_success = $this->query_success && $stmt->execute();
    $this->mysqli_error .= "\n" . $this->mysqli->error;
    $stmt->fetch();
    $stmt->close();

    if (property_exists($this->payload->data, 'timestamp')) {
      $timeStamp = $this->payload->data->timestamp;
    } elseif (property_exists($this->payload->data->body, 'telemetry')) {
      $timeStamp = (int)($this->payload->data->body->telemetry[0]->timestamp_ms / 1000);
    } else {
      $timeStamp = time();
    }

    $date = new \DateTime('@' . $timeStamp);
    $timestamp = $date->format('Y-m-d H:i:s');

    // if new item
    if ($itemId === null) {
      $itemId = $this->last_item + 1;
      $stmt = $this->mysqli->prepare('INSERT INTO item (id, user_id, project_id, level, language, id_str, type, last_occ, last_timestamp, first_in_chain, token_id) VALUES (?,?,?,?,?,?,?,1, ?,?,?);');
      $stmt->bind_param('iiisssssii', $itemId, $this->userId, $this->projectId, $this->payload->data->level, $this->payload->data->language, $this->id_str, $this->itemType, $timestamp, $this->firstInChain, $this->tokenId);
      $this->query_success = $this->query_success && $stmt->execute();
      $this->mysqli_error .= "\n" . $this->mysqli->error;
      $stmt->fetch();
      $stmt->close();

      $stmt = $this->mysqli->prepare('UPDATE project SET last_item = last_item + 1 WHERE user_id=? and id=?');
      $stmt->bind_param('ii', $this->userId, $this->projectId);
      $this->query_success = $this->query_success && $stmt->execute();
      $this->mysqli_error .= "\n" . $this->mysqli->error;
      $stmt->close();

      $last_occ = 1;
    } else {
      $stmt = $this->mysqli->prepare('UPDATE item SET last_occ = last_occ + 1, last_timestamp=? WHERE user_id=? and project_id=? and id=?');
      $stmt->bind_param('siii', $timestamp, $this->userId, $this->projectId, $itemId);
      $this->query_success = $this->query_success && $stmt->execute();
      $this->mysqli_error .= "\n" . $this->mysqli->error;
      $stmt->close();

      $last_occ++;
    }
    $this->last_item = $itemId;
    if ($traceChain){
      $this->firstInChain = $this->firstInChain === 0 ? $this->last_item : $this->firstInChain;
    }

    if ($last_occ <= $this->config->max_occurences) {
      $data = json_encode($this->payload->data);
      $data = \str_replace('$remote_ip', $this->getClientIpServer(), $data);
      $stmt = $this->mysqli->prepare('INSERT INTO occurrence (id, user_id, project_id, item_id, timestamp, data) VALUES (?,?,?,?,?,?);');
      $stmt->bind_param('iiiiss', $last_occ, $this->userId, $this->projectId, $itemId, $timestamp, $data);
      $this->query_success = $this->query_success && $stmt->execute();
      $this->mysqli_error .= "\n" . $this->mysqli->error;
      $stmt->fetch();
      $stmt->close();
    }

    /*
    $fp = fopen(__DIR__ . "/mysqli.txt", 'wb');
    fwrite($fp, $mysqli_error);
    fclose($fp);
     */


  }

  /**
   * Function to get the client ip address
   *
   * @return string
   */
  private function getClientIpServer(): string
  {
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
      $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    }
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
      $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    }
    else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
      $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    }
    else if (isset($_SERVER['HTTP_FORWARDED'])) {
      $ipaddress = $_SERVER['HTTP_FORWARDED'];
    }
    else if (isset($_SERVER['REMOTE_ADDR'])) {
      $ipaddress = $_SERVER['REMOTE_ADDR'];
    }
    else {
      $ipaddress = 'UNKNOWN';
    }

    return $ipaddress;
  }

}

