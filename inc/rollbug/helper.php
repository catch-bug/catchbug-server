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
   *
   * @return string
   */
  public function listArray(array $array, string $dtClass='col-sm-6', string $ddClass='col-sm-6'): string
  {
    $list = '<dl class="row">';

    foreach ($array as $key=>$value){
      $list .= "<dt class='$dtClass'>" . $key . '</dt>';

      if (\is_object($value) || \is_array($value)){
        $this->listArray((array) $value, $dtClass, $ddClass);

      } else {
        $list .= "<dd class='$ddClass'>" . $value . '</dd>';
      }
    }

    return $list . '</dl>';
  }

  /**
   * add active to class in bs4 navigation
   *
   * @param $section string
   * @param $item string
   *
   * @return string ' active' | ''
   */
  public function checkActive(string $section, string $item): string
  {
    return $section === $item ? ' active' : '';
  }

  /**
   * Add selected in <select><option> html tag
   *
   * @param $option string
   * @param $item string
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
}
