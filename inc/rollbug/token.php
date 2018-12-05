<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 1.12.18
 * @Time   : 23:04
 */

namespace rollbug;


class token
{
  /**
   * @var integer
   */
  public $id;

  /**
   * @var integer
   */
  public $userId;

  /**
   * @var integer
   */
  public $projectId;

  /**
   * @var string
   */
  public $token;

  /**
   * @var array
   */
  public $type = [];

  /**
   * @var string
   */
  public $types;

  /**
   * @var string
   */
  public $name;

  /**
   * @var boolean
   */
  public $disabled;

  /**
   * @var \DateTime
   */
  public $created;

  /**
   * @var \DateTime
   */
  public $lastUpdate;

  /**
   * @var string
   */
  public $rateLimitPer;

  /**
   * @var string
   */
  public $rateLimitCalls;

  /**
   * @var string
   */
  private $timeFormat = 'd.m.Y H:i:s';

  /**
   * @var \DateTimeZone
   */
  private $timeZone;

  /**
   * token constructor.
   *
   * @param \stdClass $obj
   *
   * @throws \Exception
   */
  public function __construct(\stdClass $obj)
  {
    $this->id = $obj->id;
    $this->userId = $obj->user_id;
    $this->projectId = $obj->project_id;
    $this->token = $obj->token;
    $this->type = \explode(',', $obj->type);
    $this->types = $obj->type;
    $this->name = $obj->name;
    $this->disabled = $obj->disabled > 0;
    $this->created = new \DateTime($obj->created, new \DateTimeZone('UTC'));
    $this->lastUpdate = new \DateTime($obj->last_updated, new \DateTimeZone('UTC'));
    $this->rateLimitPer = $obj->rate_limit_per;
    $this->rateLimitCalls = $obj->rate_limit_calls;

    $this->timeZone = new \DateTimeZone('UTC');
  }

  /**
   * @param string             $format
   * @param \DateTimeZone|null $timezone
   *
   * @return string
   */
  public function getCreatedStr(string $format, \DateTimeZone $timezone = null): string
  {
    return helper::formatDateTime($format, $this->created, $timezone);
  }

  /**
   * @param string             $format
   * @param \DateTimeZone|null $timezone
   *
   * @return string
   */
  public function getLastUpdatedStr(string $format, \DateTimeZone $timezone = null): string
  {
    return helper::formatDateTime($format, $this->lastUpdate, $timezone);
  }

  /**
   * @param string $timeFormat
   */
  public function setTimeFormat(string $timeFormat): void
  {
    $this->timeFormat = $timeFormat;
  }

  /**
   * @param \DateTimeZone $timeZone
   */
  public function setTimeZone(\DateTimeZone $timeZone): void
  {
    $this->timeZone = $timeZone;
  }

  /**
   * @return string
   */
  public function __toString(): string
  {
    return (string)\json_encode((object)[
        'id'             => $this->id,
        'userId'         => $this->userId,
        'projectId'      => $this->projectId,
        'token'          => $this->token,
        'name'           => $this->name,
        'types'          => $this->types,
        'disabled'       => $this->disabled,
        'created'        => $this->getCreatedStr($this->timeFormat, $this->timeZone),
        'updated'        => $this->getLastUpdatedStr($this->timeFormat, $this->timeZone),
        'rateLimitPer'   => $this->rateLimitPer,
        'rateLimitCalls' => $this->rateLimitCalls
    ]);
  }

}
