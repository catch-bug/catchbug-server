<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 28.11.18
 * @Time   : 14:09
 */

use catchbug\item;
use catchbug\occurrence;


/** @var \catchbug\user $user */
/** @var \catchbug\helper $helper */

$query = "SELECT id, project_id, level, language, id_str, type, last_occ, last_timestamp, first_in_chain FROM item WHERE user_id=$user->id and project_id=$projectId and id=$projectItem";
if ($result = $mysqli->query($query) ) {
  $obj = $result->fetch_object();
  if ($obj !== null) {
    $item = new item($obj);
    $result->close();

    $query = "SELECT id, project_id, timestamp, data FROM occurrence WHERE user_id=$user->id and project_id=$projectId and item_id=$projectItem and user_id=$user->id and id=$itemOccurrenceId";
    if ($result = $mysqli->query($query)) {
      $obj = $result->fetch_object();
      if ($obj !== null) {
        $item->occurrences[ $obj->id ] = new occurrence($obj);


// ----------------------------------- curl replay command
        $curlReplayCode = '';
        $contentType = '';
        if ($item->getFirstOcc()->getRequestMethod() !== '') {
          $curlReplayCode = '<hr><h3>Replay curl command</h3><pre>';
          $curlReplayCode .= "curl --request {$item->getFirstOcc()->getRequestMethod()} '{$item->getFirstOcc()->getURL()}'";
          // headers
          if (property_exists($item->getFirstOcc()->data->request, 'headers')) {
            foreach ((array)$item->getFirstOcc()->data->request->headers as $key => $value) {
              $curlReplayCode .= " \\\n";
              $curlReplayCode .= "--header '$key: $value'";
              if ($key === 'Content-Type'){
                $contentType = $value;
              }
            }
          }
          // data
          if ($contentType === 'application/x-www-form-urlencoded'){
            // data from post array
            $curlReplayCode .= " \\\n";
            $curlReplayCode .= "--data '" . http_build_query($item->getFirstOcc()->getPostArray(), '', '&amp;') . "'";
          } else {
            // send raw body data if exists
            if (property_exists($item->getFirstOcc()->data->request, 'body')
                && $item->getFirstOcc()->data->request->body !== '') {
              $curlReplayCode .= " \\\n";
              $curlReplayCode .= "--data '{$item->getFirstOcc()->data->request->body}'";
            }
          }
          $curlReplayCode .= '</pre>';
        }

// ----------------------------------- page content
        $content .= <<<HTML
<h4><a href="{$config->rewrite}project/$projectId/item/{$item->id}">#{$item->id}</a> <span class="text-danger">{$item->exceptionClass}:</span> {$item->getFirstOcc()->getExceptionMessage(true)}</h4>
<hr>
<div class="d-flex flex-row mb-3">
<div>
<form class="form-inline">
<label class="my-1 mr-2" for="selectLevel">Level:</label>
<select class="custom-select custom-select-sm my-1 mr-sm-2" id="selectLevel" data-projectid="{$item->projectId}" data-userid="$user->id" data-itemid="{$item->id}">
<option value="critical" {$helper->checkSelected('ctitical', $item->level)}>Critical</option>
<option value="error" {$helper->checkSelected('error', $item->level)}>Error</option>
<option value="warning" {$helper->checkSelected('warning', $item->level)}>Warning</option>
<option value="info" {$helper->checkSelected('info', $item->level)}>Info</option>
<option value="debug" {$helper->checkSelected('debug', $item->level)}>Debug</option>
</select>
</form>
</div>

<div class="ml-auto"><button type="button float-right" class="btn btn-danger" id="btnDeleteOccurrence" data-projectid="{$item->projectId}" data-userid="$user->id" data-itemid="{$item->id}" data-occurrenceid="{$item->getFirstOcc()->id}">Delete Occurrence</button></div>
</div>
<hr>
{$item->getTracebackHTML()}

<hr>
<h3>Params</h3>
{$helper->listArray((array)$item->getFirstOcc()->data, 'col-sm-2', 'col-sm-10', 'body')}

$curlReplayCode

<hr>
<h3>Raw JSON</h3>
<pre><code class="language-json">{$item->getFirstOcc()->getRawJSON(true)}</code></pre>
<script src="/js/prism.js"></script>
HTML;

      } else {
        $content .= <<<HTML
<h4 class="text-danger">Occurrence not found</h4>
HTML;
      }
    }
  } else {
    $content .= <<<HTML
<h4 class="text-danger">Item not found</h4>
HTML;
  }
}


