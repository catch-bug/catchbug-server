<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 22.11.18
 * @Time   : 13:12
 */

namespace rollbug;


class project
{
  /**
   * @var integer project id
   */
  private $id;

  /**
   * @var string project name
   */
  private $name;


  /**
   * @var integer last item
   */
  private $lastItem;


  // -----------------------------------

  /**
   * project constructor.
   *
   * @param int    $id
   * @param string $name
   * @param int    $lastItem
   */
  public function __construct(int $id, string $name, int $lastItem)
  {
    $this->id = $id;
    $this->name = $name;
    $this->lastItem = $lastItem;
  }


  /**
   * @return int
   */
  public function getId(): int
  {
    return $this->id;
  }

  /**
   * @param int $id
   *
   * @return project
   */
  public function setId(int $id): project
  {
    $this->id = $id;
    return $this;
  }

  /**
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * @param string $name
   *
   * @return project
   */
  public function setName(string $name): project
  {
    $this->name = $name;
    return $this;
  }

  /**
   * @return int
   */
  public function getLastItem(): int
  {
    return $this->lastItem;
  }

  /**
   * @param int $lastItem
   *
   * @return project
   */
  public function setLastItem(int $lastItem): project
  {
    $this->lastItem = $lastItem;
    return $this;
  }


}
