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
   * @var  \rollbug\settingsBase::$data
   */
  private static $data;
  private $settingsFileName;
  private static $version = '1.0.0-dev';

  /**
   * config constructor.
   *
   * @param bool   $checkUpdate
   * @param bool   $getDefaultValues get default values for settings changes
   * @param string $settingsFileName
   *
   * @throws \Exception
   */
  public function __construct(bool $checkUpdate=false, bool $getDefaultValues=false, string $settingsFileName =  __DIR__ . '/../../settings.php')
  {
    $this->settingsFileName = $settingsFileName;
    @$success = include $settingsFileName;
    if (!$success) {
      throw new \RuntimeException('Can not load configuration file: ' . $settingsFileName, 1);
    }
    if ($getDefaultValues) {
      $baseSettings = new settingsBase();
      self::$data = $this->toObject(array_replace_recursive($this->toArray($baseSettings->getData()), $this->toArray($success)));
    } else {
      self::$data = $success;
    }

    $this->version = self::$version;

    if ($checkUpdate) {
      $this->checkNewVersion();
    }
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
   * @return mixed
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
    return isset(self::$data->$key);
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
    if (function_exists('opcache_invalidate')) {
      opcache_invalidate($filename, true);
    } elseif (function_exists('apc_compile_file')) {
      apc_compile_file($filename);
    }
  }


  /**
   *
   * @return bool
   */
  public function isNewVersion(): bool
  {
    $version = \substr(self::$version, 1);

    return \version_compare(\strtolower($version), \strtolower($this->latest_version), '<');
  }


  // ----------------------------------- // ----------- Private funstions -------- // -----------------------------------
  /**
   * @return string
   */
  private static function getLatestVersion(): string
  {
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: PHP-RollBugServer'
            ]
        ]
    ];

    $context = stream_context_create($opts);
    if ($json = \file_get_contents('https://api.github.com/repos/rollbug/rollbug-server/releases/latest', false, $context)) {
      $apiLatest = \json_decode($json);

      if (isset($apiLatest->tag_name)) {
        return \substr($apiLatest->tag_name, 1);
      }
    }

    return '0.0.0';
  }

  /**
   * @throws \Exception
   */
  private function checkNewVersion(): void
  {
    if (!isset($this->latest_version_check)) {
      $this->latest_version_check = \time();
    }
    /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
    if ($this->latest_version_check + 86400 < \time()) {
      $latest = self::getLatestVersion();
      $this->latest_version_check = \time();
      $this->latest_version = $latest;

      $this->saveData();
    }
  }

  /**
   * @param \stdClass $object
   *
   * @return array
   */
  private function toArray(\stdClass $object): array
  {
    return json_decode(json_encode($object), true);
  }

  /**
   * @param array $array
   *
   * @return \stdClass
   */
  private function toObject(array $array): \stdClass
  {
    return json_decode(json_encode($array, JSON_FORCE_OBJECT), false);
  }
}
