<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 30.11.18
 * @Time   : 0:57
 */

/** @var \rollbug\user $user */
/** @var \rollbug\helper $helper */



$userSettingsMenu = strtok('/');
$userSettingsMenu = $userSettingsMenu === false ? 'general' : $userSettingsMenu;

$userSettingsContent = '';
switch ($userSettingsMenu) {
  case 'general':
    include __DIR__ . '/section_user_settings_general.php';
    break;

  case 'auth':
    include __DIR__ . '/section_user_settings_auth.php';
    break;

  case 'emails':
    include __DIR__ . '/section_user_settings_emails.php';
    break;

  case 'delete':
//include __DIR__ . '/section_user_settings_delete.php'; // don't delete last root
    break;
}

// settings left menu
$content .= <<<HTML
<h2>User Settings</h2>
<div class="row">
  <div class="col-sm-3">
    <ul class="nav flex-column nav-settings">
      <li class="nav-item">
        <a class="nav-link {$helper->checkActive('general', $userSettingsMenu)}" href="{$config->rewrite}user/settings/general">General</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {$helper->checkActive('auth', $userSettingsMenu)}" href="{$config->rewrite}user/settings/auth">Authentication</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {$helper->checkActive('emails', $userSettingsMenu)}" href="{$config->rewrite}user/settings/emails">Emails</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {$helper->checkActive('delete', $userSettingsMenu)} text-danger" href="{$config->rewrite}user/settings/delete">Delete Account</a>
      </li>
    </ul>

  </div>
  <div class="col-sm-9">
    $userSettingsContent
  </div>
</div>
HTML;
