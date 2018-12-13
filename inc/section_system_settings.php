<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 30.11.18
 * @Time   : 0:57
 */


/** @var \rollbug\user $user */
/** @var \rollbug\helper $helper */



$systemSettingsMenu = strtok('/');
$systemSettingsMenu = $systemSettingsMenu === false ? 'general' : $systemSettingsMenu;

$systemSettingsContent = '';
switch ($systemSettingsMenu) {
  case 'general':
    include __DIR__ . '/section_system_settings_general.php';
    break;

  case 'users':
    include __DIR__ . '/section_system_settings_users.php';
    break;

}

// settings left menu
$content .= <<<HTML
<h2>System Settings</h2>
<div class="row">
  <div class="col-sm-3">
    <ul class="nav flex-column nav-settings">
      <li class="nav-item">
        <a class="nav-link {$helper->checkActive('general', $systemSettingsMenu)}" href="{$config->rewrite}system/general">General</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {$helper->checkActive('users', $systemSettingsMenu)}" href="{$config->rewrite}system/users">Users</a>
      </li>
    </ul>

  </div>
  <div class="col-sm-9">
    $systemSettingsContent
  </div>
</div>
HTML;


$javascriptContent = <<<JS
var script = document.createElement("script");
    script.src = "/js/system_settings.js";
    document.body.appendChild(script);
JS;

