<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 10.12.18
 * @Time   : 22:51
 */

/** @var \catchbug\user $user */
/** @var \catchbug\helper $helper */


if ($user->root) {
  $stmt = $mysqli->prepare('SELECT count(id) FROM user WHERE root!=0');
  $stmt->bind_result($count);
  $stmt->execute();
  $stmt->fetch();
  $stmt->close();

  $accountDeletable = $count > 1;
} else {
  $accountDeletable = true;
}

$deleteButton = $accountDeletable ? "<button type='button' class='btn btn-sm btn-danger' data-user-id='$user->id' data-user-name='$user->name' id='btnDeleteAccount'>Delete account</button>" : '';

$userSettingsContent= <<<HTML
<h3 class="text-danger">Delete account</h3>
$deleteButton
HTML;
