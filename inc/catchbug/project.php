<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 22.11.18
 * @Time   : 13:12
 */

namespace catchbug;


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
   * @var string project descroption
   */
  private $description;

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
   * @param string $description
   * @param int    $lastItem
   */
  public function __construct(int $id, string $name, string $description, int $lastItem)
  {
    $this->id = $id;
    $this->name = $name;
    $this->description = $description;
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

  /**
   * @return string
   */
  public function getDescription(): string
  {
    return $this->description;
  }

  /**
   * @param string $description
   *
   * @return project
   */
  public function setDescription(string $description): project
  {
    $this->description = $description;
    return $this;
  }




}
