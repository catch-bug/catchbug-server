<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 22.11.18
 * @Time   : 14:56
 */

/** @var \rollbug\user $user */
/** @var \rollbug\helper $helper */

$content .= <<<HTML
<h2>welcome {$user->name}</h2>
HTML;
