<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 22.11.18
 * @Time   : 15:25
 */

namespace rollbug;

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
   * @var int
   */
  public $firstInChain = 0;

  /**
   * @var \rollbug\occurrence
   */
  public $occurrences = array();

  /**
   * browser array 'browser name' => 'count'
   * @var array
   */
  public $browsers = [];

  /**
   * OS array 'os name' => 'count'
   * @var array
   */
  public $oss = [];

  /**
   * IP array 'user IP' => 'count'
   * @var array
   */
  public $userIPs = [];

  /**
   * item constructor.
   *
   * @param \stdClass $item id, project_id, level, language, id_str, last_occ, last_timestamp
   *
   * @throws \Exception
   */
  public function __construct(\stdClass $item)
  {
    $this->id = $item->id;
    $this->projectId = $item->project_id;
    $this->level = $item->level;
    $this->type = $item->type;
    $this->language = $item->language;
    $this->lastOcc = $item->last_occ;
    $this->firstInChain = $item->first_in_chain;
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

  /**
   * @param bool $force
   */
  public function countBrowsers(bool $force=false): void
  {
    if(!$force && \count($this->browsers) === 0){
      /** @var \rollbug\occurrence $occurrence */
      foreach ($this->occurrences as $occurrence){
        $browserId = $occurrence->browser->getName() . ' ' . $occurrence->browser->getVersion();
        if (\array_key_exists($browserId, $this->browsers)){
          $this->browsers[$browserId]++;
        } else {
          $this->browsers[$browserId] = 1;
        }
      }
    }
  }

  /**
   * @param bool $force
   */
  public function countOs(bool $force=false): void
  {
    if(!$force && \count($this->oss) === 0){
      /** @var \rollbug\occurrence $occurrence */
      foreach ($this->occurrences as $occurrence){
        $osId = $occurrence->os->getName() . ' ' . $occurrence->os->getVersion();
        if (\array_key_exists($osId, $this->oss)){
          $this->oss[$osId]++;
        } else {
          $this->oss[$osId] = 1;
        }
      }
    }
  }

  /**
   * @param bool $force
   */
  public function countUserIP(bool $force=false): void
  {
    if(!$force && \count($this->userIPs) === 0){
      /** @var \rollbug\occurrence $occurrence */
      foreach ($this->occurrences as $occurrence){
        $userIP = $occurrence->getUserIP();
        if (\array_key_exists($userIP, $this->userIPs)){
          $this->userIPs[$userIP]++;
        } else {
          $this->userIPs[$userIP] = 1;
        }
      }
    }
  }

  /**
   * @param bool $htmlSafe
   *
   * @return string
   */
  public function getExceptionMessage(bool $htmlSafe=false): string
  {
    if ($htmlSafe){
      return \htmlentities($this->exceptionMessage, ENT_QUOTES);
    }
    return $this->exceptionMessage;
  }

  /**
   * @return string
   */
  public function getTracebackHTML(): string
  {
    $tracebackContent = <<<HTML
<p><span class="text-danger">{$this->exceptionClass}:</span> {$this->getFirstOcc()->getExceptionMessage(true)}</p>
HTML;

    if ($this->type === 'trace') {

      $frames = $this->getFirstOcc()->data->body->trace->frames;
      $tracebackContent .= '<div class="text-monospace">';
      foreach ($frames as $id => $frame) {
        $tracebackContent .= str_replace(' ', '&nbsp;', sprintf('%3s ', $id)) .
            '<span class="text-black-50">File</span> ' . $frame->filename . ' ' .
            (property_exists($frame, 'lineno') ? '<span class="text-black-50">line</span> ' . $frame->lineno . ' ' : '') .
            (property_exists($frame, 'colno') ? '<span class="text-black-50">col.</span> ' . $frame->colno . ' ' : '') .
            (property_exists($frame, 'method') ? '<span class="text-black-50">in</span> <code>' . $frame->method . '</code> ' : '') .
            (property_exists($frame, 'code') ? '<span class="text-black-50">code <code>' . $frame->code . '</code> ' : '') .
            (property_exists($frame, 'class_name') ? '<span class="text-black-50">class</span> ' . $frame->class_name . ' ' : '') . '<br>';
      }
      $tracebackContent .= '</div>';
    }

    return $tracebackContent;
  }


}
