<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 6.12.18
 * @Time   : 3:41
 */
$contentSettings = '';

$projectSettingsMenu = strtok('/');
$projectSettingsMenu = $projectSettingsMenu === false ? 'general' : $projectSettingsMenu;

$projectSettingsContent = '';
switch ($projectSettingsMenu){
  case 'general':
    include __DIR__ . '/section_project_settings_general.php';
    break;

  case 'members':
    include __DIR__ . '/section_project_settings_members.php';
    break;

  case 'tokens':
    include __DIR__ . '/section_project_settings_tokens.php';
    break;

  case 'delete':
    include __DIR__ . '/section_project_settings_delete.php';
    break;
}

// settings left menu
$contentSettings .= <<<HTML
<div class="row">
<div class="col-sm-3">
<ul class="nav flex-column nav-settings">
  <li class="nav-item">
    <a class="nav-link {$helper->checkActive('general', $projectSettingsMenu)}" href="{$config->rewrite}project/$projectId/settings/general">General</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {$helper->checkActive('members', $projectSettingsMenu)}" href="{$config->rewrite}project/$projectId/settings/members">Members</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {$helper->checkActive('tokens', $projectSettingsMenu)}" href="{$config->rewrite}project/$projectId/settings/tokens">Project Access Tokens</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {$helper->checkActive('delete', $projectSettingsMenu)} text-danger" href="{$config->rewrite}project/$projectId/settings/delete">Delete Project</a>
  </li>
</ul>

</div>
<div class="col-sm-9">
$projectSettingsContent
</div>
</div>
HTML;


return $contentSettings;
