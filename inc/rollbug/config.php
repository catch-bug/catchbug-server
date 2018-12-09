<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 19.11.18
 * @Time   : 16:02
 */
namespace rollbug;

class config
{
  /**
   * @var $data = (object)[
   *       'database' => (object)[
   *               'server' => 'localhost',
   *               'user' => 'rollbar',
   *               'pass' => 'prdel',
   *               'database' => 'rollbar',
   *               'port' => '5719',
   *               'socket' => '/tmp/mysql_sandbox5719.sock',
   *        ],
   *       'max_occurences' => 10,
   *       'rewrite' => '/', // '/' - rewrite is on; '/?' - rewrite is off
   *       'version' => ''  // version from source code
   *   ]
   *
   */
  private static $data;
  private $settingsFileName;
  private static $version = '1.0.0-dev';

  /**
   * config constructor.
   *
   * @param string $settingsFileName
   * @throws \Exception
   */
  public function __construct(string $settingsFileName =  __DIR__ . '/../../settings.php')
  {
    $this->settingsFileName = $settingsFileName;
    @$success = include $settingsFileName;
    if (!$success) {
      throw new \RuntimeException('Application settings not found.', 1);
    }
    self::$data = $success;
    $this->version = self::$version;
  }

  /**
   * @return mixed
   */
  public function getData()
  {
    return self::$data;
  }

  /**
   * @param $key string
   *
   * @return self::$data
   * @throws \Exception
   */
  public function __get(string $key)
  {
    if (isset(self::$data->$key)) {
      return self::$data->$key;
    }

    throw new \RuntimeException('Configuration property '. $key . ' not exists.', 2);
  }

  /**
   * @param $key    string
   * @param $value  mixed
   */
  public function __set(string $key, $value)
  {
    self::$data->$key = $value;
  }

  /**
   * @param $key string
   *
   * @return bool
   */
  public function __isset(string $key)
  {
    return isset(self::$data[$key]);
  }


  /**
   * @param string $filename
   *
   * @throws \Exception
   */
  public function saveData(string $filename = null): void
  {
    $filename = $filename ?? $this->settingsFileName;
    $filename = \realpath($filename);
    if (@file_put_contents($filename, "<?php \nreturn \n" . \str_replace('stdClass::__set_state', '(object)', var_export(self::$data, true)) . ';') === false ){
      throw new \RuntimeException('Error writing configuration file: ' . $filename, 3);
    }
  }
}
