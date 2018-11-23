<?php
/**
 *
 * Copyright (c) 2016. Mira
 * http://z-web.eu
 * all rights reserved
 * Last update: 30.11.16 18:00
 *
 */

/**
 * Zabezpeceni ajax odpovedi
 *
 * pouziti
 *
 * do index.php:
 *
 *  <?php
 *    $ajax_token = md5(rand(1000,9999));   // libovolnej nahodnej nesmysl
 *    $_SESSION['ajax_token'] = $ajax_token;
 *  ?>
 *
 * do formulare pridat:
 *
 *     <input type="hidden" name="ajax_token" value="<?php echo $_SESSION['ajax_token'] ?>" />
 *
 * nebo do volani ajax pozadavku:
 *
 *     ajax_token=<?php echo $_SESSION['ajax_token'] ?>
 *
 * todo dodelat dokumentaci
 *
 * @Project: wexportal
 * @User: mira
 * @Date: 2.10.14
 * @Time: 13:16
 * *
 */
declare(strict_types=1);
function secure()
{
  $acess = false;
  if (!is_session_started()){
    session_start();
  }

  if ($_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    die('Unauthorized access!');
  }
  if (isset($_GET['ajax_token']) && ($_GET['ajax_token'] === @$_SESSION['ajax_token'])) {
    $acess = true;
  }
  if (isset($_POST['ajax_token']) && ($_POST['ajax_token'] === @$_SESSION['ajax_token'])) {
    $acess = true;
  }
  if (!$acess) {
 die('Unauthorized access!!');
  }
}
if (!function_exists('is_session_started')) {
  function is_session_started():bool
  {
    if (php_sapi_name() !== 'cli') {
      if (version_compare(phpversion(), '5.4.0', '>=')) {
        return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
      } else {
        return session_id() === '' ? FALSE : TRUE;
      }
    }
    return FALSE;
  }
}
secure();
