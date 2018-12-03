<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 30.11.18
 * @Time   : 0:57
 */

/** @var \rollbug\user $user */
/** @var \rollbug\helper $helper */

$projectSettingsContent = <<<HTML
<h3 class="text-danger">Delete Project {$user->getProject($projectId)->getName()}</h3>
HTML;

