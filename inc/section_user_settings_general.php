<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 5.12.18
 * @Time   : 5:03
 */

/** @var \catchbug\user $user */
/** @var \catchbug\helper $helper */

$userSettingsContent= <<<HTML
<h3>General settings</h3>
<div class="row"><img src="{$user->getGravatarImgLink()}" alt="Avatar" class="src"></div>
HTML;
