<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 22.11.18
 * @Time   : 13:07
 */

namespace rollbug;


class user
{
  /**
   * @var integer user id
   */
  public $id;

  /**
   * @var string user name
   */
  public $name;

  /**
   * @var boolean
   */
  public $root;

  /**
   * @var project
   */
  public $projects = array();

  /**
   * @var item
   */
  public $items = array();

  /**
   * @var \DateTimeZone user time zone
   */
  public $DateTimeZone;

  /**
   * @var \mysqli
   */
  private $mysqli;


  public function __construct(int $id, \mysqli $mysqli)
  {
    $this->id = $id;
    $this->mysqli = $mysqli;
    //get user data
    $stmt = $mysqli->prepare('SELECT name, root, time_zone FROM user WHERE id=?');
    $stmt->bind_param('i', $this->id);
    $stmt->bind_result($this->name, $this->root, $time_zone);
    $stmt->execute();
    $stmt->fetch();
    $stmt->close();

    $this->DateTimeZone = new \DateTimeZone($time_zone);

    // get user projects
    $stmt = $mysqli->prepare('SELECT id, name, last_item FROM project WHERE user_id=?');
    $stmt->bind_param('i', $id);
    $stmt->bind_result($projectId, $projectName, $projectLastItem);
    $stmt->execute();
    while ($stmt->fetch()){
      $this->addProject($projectId, $projectName, $projectLastItem);
    }
    $stmt->close();
  }

  /**
   * @param int $project project id
   *
   * @return \rollbug\user
   */
  public function setItems(int $project): user
  {
    if ($project !== 0) {
      $query = "SELECT id, project_id, level, language, id_str, last_occ, type, last_timestamp, first_in_chain FROM item WHERE user_id=$this->id and project_id=$project order by id desc";
    } else {
      $query = "SELECT id, project_id, level, language, id_str, last_occ, type, last_timestamp, first_in_chain FROM item WHERE user_id=$this->id order by id desc";
    }

    if ($result = $this->mysqli->query($query)) {
      while ($obj = $result->fetch_object()) {
        $this->items[$obj->id] = new item($obj);
      }
      $result->close();
    }
    return $this;
  }

  /**
   * @param int $id item id
   *
   * @return item
   */
  public function getItem(int $id): item
  {
    return $this->items[$id];
  }



  /**
   * @param int    $id
   * @param string $name
   * @param int    $lastItem
   *
   * @return \rollbug\user
   */
  public function addProject(int $id, string $name, int $lastItem): user
  {
    $this->projects[$id] = new project($id, $name, $lastItem);
    return $this;
  }

  /**
   * @param int $id project id
   *
   * @return project
   */
  public function getProject(int $id): project
  {
    return $this->projects[$id];
  }

  /**
   * @param int $id
   *
   * @return bool
   */
  public function isProject(int $id): bool
  {
    return isset($this->projects[$id]);
  }

  /**
   * @return int
   */
  public function getId(): int
  {
    return $this->id;
  }


}
