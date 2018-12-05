<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 1.12.18
 * @Time   : 21:28
 */

$content .=<<<HTML
<h2>New Project</h2>
HTML;

include __DIR__ . '/section_project_settings_general.php';

$content .= $projectSettingsForm;
