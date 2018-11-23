<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 22.11.18
 * @Time   : 14:56
 */
/*
echo '<pre>';
var_dump($projectId);
echo '</pre>';
*/

/** @var \rollbug\user $user */

$user->setItems($projectId);

$tableBody = '';
/** @var \rollbug\item $item */
foreach ($user->items as $item){
  $item->lastTimestamp->setTimezone($user->DateTimeZone);
  $tableBody .= <<<HTML
<tr>
<td>{$item->lastOcc}</td>
<td>{$item->lastTimestamp->format('d.m.Y H:i:s e')}</td>
<td><a href="?/project/{$item->projectId}/item/{$item->id}">#{$item->id} {$item->exceptionClass}: {$item->exceptionMessage}</a></td>
<td>{$item->level}</td>
</tr>
HTML;
}

$content .= <<<HTML
<div class="table-responsive">
<table class="table table-hover table-sm ">
<thead class="thead-dark">
<tr>
<th scope="col">Total</th>
<th scope="col">Last</th>
<th scope="col">Item</th>
<th scope="col">Level</th>
</tr>
</thead>
<tbody>$tableBody</tbody>
</table>
</div>
HTML;

