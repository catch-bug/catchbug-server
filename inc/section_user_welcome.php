<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 22.11.18
 * @Time   : 14:56
 */

/** @var \catchbug\user $user */
/** @var \catchbug\helper $helper */

$content .= <<<HTML
<h2>welcome {$user->name}</h2>
HTML;
