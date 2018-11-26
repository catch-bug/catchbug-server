<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 22.11.18
 * @Time   : 15:25
 */

namespace rollbug;

require_once __DIR__ . '/occurrence.php';

class item
{
  /**
   * @var integer item id
   */
  public $id;

  /**
   * @var integer project id
   */
  public $projectId;

  /**
   * @var string error level
   */
  public $level;

  /**
   * @var string 'trace' | 'message' | 'crash_report'
   */
  public $type;

  /**
   * @var string programing language
   */
  public $language;

  /**
   * @var string file
   */
  public $file;

  /**
   * @var integer line no
   */
  public $line;

  /**
   * @var string
   */
  public $exceptionClass;

  /**
   * @var string
   */
  public $exceptionMessage;

  /**
   * @var integer last occurrence id
   */
  public $lastOcc;

  /**
   * @var \DateTime
   */
  public $lastTimestamp;

  /**
   * @var \rollbug\occurrence
   */
  public $occurrences = array();

  /**
   * item constructor.
   *
   * @param \stdClass $item id, project_id, level, language, id_str, last_occ, last_timestamp
   */
  public function __construct(\stdClass $item)
  {
    $this->id = $item->id;
    $this->projectId = $item->project_id;
    $this->level = $item->level;
    $this->type = $item->type;
    $this->language = $item->language;
    $this->lastOcc = $item->last_occ;
    $this->lastTimestamp = new \DateTime( $item->last_timestamp, new \DateTimeZone('UTC'));
    $this->file = \strtok($item->id_str, '|');
    $this->line = \strtok('|');
    $this->exceptionClass = \strtok('|');
    $this->exceptionMessage = \strtok('|');
  }

  /**
   * @param string             $format
   * @param \DateTimeZone|null $timezone null = UTC
   *
   * @return \DateTime
   */
  public function getLastTimestampStr(string $format, \DateTimeZone $timezone=null): string
  {
    if ($timezone === null){
      $this->lastTimestamp->setTimezone(new \DateTimeZone('UTC'));
    } else {
      $this->lastTimestamp->setTimezone($timezone);
    }
    return $this->lastTimestamp->format($format);
  }

  /**
   * @param string             $format
   * @param \DateTimeZone|null $timezone
   *
   * @return string
   */
  public function getFirstTimestampStr(string $format, \DateTimeZone $timezone=null): string
  {
    $occurrence = $this->getFirstOcc();
    if ($timezone === null){
      $occurrence->timestamp->setTimezone(new \DateTimeZone('UTC'));
    } else {
      $occurrence->timestamp->setTimezone($timezone);
    }
    return $occurrence->timestamp->format($format);
  }

  /**
   * @return \rollbug\occurrence
   */
  public function getFirstOcc(): \rollbug\occurrence
  {
    // mysql sorting from last to first (desc)
    return end($this->occurrences);
  }



}
