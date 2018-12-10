<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 10.12.18
 * @Time   : 0:58
 */

namespace rollbug;

class userEmail
{
  /**
   * @var integer
   */
  public $id;

  /**
   * @var string
   */
  public $email;

  /**
   * @var boolean
   */
  public $verified;

  /**
   * @var boolean
   */
  public $main;

  /**
   * @var string
   */
  public $hash;

  /**
   * userEmail constructor.
   *
   * @param \stdClass $obj
   */
  public function __construct(\stdClass $obj)
  {
    $this->id = (integer) ($obj->id ?? 0);
    $this->email = $obj->email ?? '';
    $this->verified = ($obj->verified ?? 0) > 0;
    $this->main = ($obj->main ?? 0) > 0;
    $this->hash = $obj->hash ?? '';
  }

  /**
   * @param int $userId
   *
   * @return string
   */
  public function getVerifyLink(int $userId): string
  {
    if ($this->verified){
      return '';
    }

    return "<button type='button' class='btn btn-sm btn-link btn-verify-email' data-id='$this->id' data-email='$this->email' data-user-id='$userId'>Send verification email</button>";
  }


}
