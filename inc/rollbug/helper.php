<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 27.11.18
 * @Time   : 17:07
 */

namespace rollbug;


class helper
{

  /**
   * @param array  $array
   * @param string $dtClass
   * @param string $ddClass
   * @param string $excludeKey
   *
   * @return string
   */
  public function listArray(array $array, string $dtClass = 'col-sm-6', string $ddClass = 'col-sm-6', string $excludeKey=''): string
  {
    $list = '<dl class="row">';

    foreach ($array as $key => $value) {
      if ($key !== $excludeKey) {
        $list .= "<dt class='$dtClass'>" . $key . '</dt>';

        if (\is_object($value) || \is_array($value)) {
          $list .= "<dd class='$ddClass'>" . $this->listArray((array)$value, $dtClass, $ddClass) . '</dd>';

        } else {
          $list .= "<dd class='$ddClass'>" . $value . '</dd>';
        }
      }
    }

    return $list . '</dl>';
  }

  /**
   * add active to class in bs4 navigation
   *
   * @param $section mixed
   * @param $item    mixed
   *
   * @return string ' active' | ''
   */
  public function checkActive($section, $item): string
  {
    return $section === $item ? ' active' : '';
  }

  /**
   * Add selected in <select><option> html tag
   *
   * @param $option string
   * @param $item   string
   *
   * @return string ' selected' | ''
   */
  public function checkSelected(string $option, string $item): string
  {
    return $option === $item ? ' selected' : '';
  }

  /**
   * @param bool $disabled
   *
   * @return string
   */
  public function disabled(bool $disabled): string
  {
    return $disabled ? ' disabled' : '';
  }

  /**
   * Helper function for tab name in heredoc
   *
   * @param $type string
   *
   * @return string
   */
  public function tabTracebackName(string $type): string
  {
    switch ($type) {
      case 'trace':
        return 'Traceback';
        break;

      case 'message':
        return 'Message';
        break;

      case 'crash_report':
        return 'Crash Report';
        break;
    }
    return '';
  }

  /**
   * format DateTime
   *
   * @param string             $format
   * @param \DateTime          $date
   * @param \DateTimeZone|null $timezone
   *
   * @return string
   */
  public static function formatDateTime(string $format, \DateTime $date, \DateTimeZone $timezone=null): string
  {
    if ($timezone === null){
      $date->setTimezone(new \DateTimeZone('UTC'));
    } else {
      $date->setTimezone($timezone);
    }
    return $date->format($format);
  }
}
