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
Here you can delete project. <span class="text-danger">All data will be deleted.</span> There is no undo.
<hr>
<button type="button" class="btn btn-danger" id="btnDeleteProject" data-project-id="$projectId" data-project-name="{$user->getProject($projectId)->getName()}" data-user-id="$user->id">Delete This Project</button>
HTML;

