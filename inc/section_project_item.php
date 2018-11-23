<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 22.11.18
 * @Time   : 19:42
 */

use rollbug\item;
use rollbug\occurrence;

/** @var \rollbug\user $user */

$query = "SELECT id, project_id, level, language, id_str, last_occ, last_timestamp FROM item WHERE user_id=$user->id and project_id=$projectId and id=$projectItem";
if ($result = $mysqli->query($query)) {
  $obj = $result->fetch_object();
  $item  = new item($obj);
  $result->close();

  $query = "SELECT id, project_id, timestamp, data FROM occurence WHERE user_id=$user->id and project_id=$projectId and item_id=$projectItem and user_id=$user->id order by id desc";
  if ($result = $mysqli->query($query)) {
    while ($obj = $result->fetch_object()){
      $item->occurrence[$obj->id] = new occurrence($obj);
    }
  }
}
/*
echo '<pre>';
var_dump($item);
echo '</pre>';
*/

$content .= <<<HTML
<h4>#{$item->id} {$item->exceptionClass}: {$item->exceptionMessage}</h4>
<hr>
<form class="form-inline">
<label class="my-1 mr-2" for="selectLevel">Level:</label>
<select class="custom-select custom-select-sm my-1 mr-sm-2" id="selectLevel">
<option value="critical">Critical</option>
<option value="error">Error</option>
<option value="warning">Warning</option>
<option value="info">Info</option>
<option value="debug">Debug</option>
</select>
</form>

<div class="bd-example">
<form class="form-inline">
  <label class="my-1 mr-2" for="inlineFormCustomSelectPref">Preference</label>
  <select class="custom-select my-1 mr-sm-2" id="inlineFormCustomSelectPref">
    <option selected>Choose...</option>
    <option value="1">One</option>
    <option value="2">Two</option>
    <option value="3">Three</option>
  </select>

  <div class="custom-control custom-checkbox my-1 mr-sm-2">
    <input type="checkbox" class="custom-control-input" id="customControlInline">
    <label class="custom-control-label" for="customControlInline">Remember my preference</label>
  </div>

  <button type="submit" class="btn btn-primary my-1">Submit</button>
</form>
</div>
HTML;
