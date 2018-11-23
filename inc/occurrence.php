<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 22.11.18
 * @Time   : 19:56
 */

namespace rollbug;


class occurrence
{
  /**
   * @var int occurence id
   */
  public $id;

  /**
   * @var \DateTime
   */
  public $timestamp;

  /**
   * @var \stdClass
   */
  public $data;

  public function __construct($obj)
  {
    $this->id = $obj->id;
    $this->timestamp = new \DateTime( $obj->timestamp, new \DateTimeZone('UTC'));
    $this->data = \json_decode($obj->data);
  }

}
