<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 22.11.18
 * @Time   : 19:42
 */

use rollbug\item;
use rollbug\occurrence;

/** @var \rollbug\user $user */
/** @var \rollbug\helper $helper */

$query = "SELECT id, project_id, level, language, id_str, type, last_occ, last_timestamp, first_in_chain FROM item WHERE user_id=$user->id and project_id=$projectId and id=$projectItem";
if ($result = $mysqli->query($query) ) {
  $obj = $result->fetch_object();
  if ($obj !== null) {
    $item = new item($obj);
    $result->close();

    $query = "SELECT id, project_id, timestamp, data FROM occurrence WHERE user_id=$user->id and project_id=$projectId and item_id=$projectItem and user_id=$user->id order by id desc";
    if ($result = $mysqli->query($query)) {
      while ($obj = $result->fetch_object()) {
        if ($obj !== null) {
          $item->occurrences[ $obj->id ] = new occurrence($obj);
        }
      }

      /*
      echo '<pre>';
      var_dump($item);
      echo '</pre>';
      */

      // ----------------------------------- traceback / message
      $tabTracebackContent = $item->getTracebackHTML();


      // ----------------------------------- occurrences
      $tabOccurrencesContent = '';
      if (($item->type === 'trace') || ($item->type === 'message')) {
        $tabOccurrencesContentBody = '';

        /** @var \rollbug\occurrence $occurrence */
        foreach ($item->occurrences as $id => $occurrence) {
          $tabOccurrencesContentBody .= '<tr>';
          // Timestamp
          $tabOccurrencesContentBody .= "<td><a href=\"{$config->rewrite}project/$projectId/item/{$item->id}/occurrence/$id\">{$occurrence->getTimestampStr('d.m.Y H:i:s', $user->DateTimeZone)}</a> </td>";
          // Browser
          $tabOccurrencesContentBody .= "<td>{$occurrence->browser->getImg(24)}</td>";
          // OS
          $tabOccurrencesContentBody .= "<td>{$occurrence->os->getImg(24)}</td>";
          // Req. Method
          $tabOccurrencesContentBody .= "<td>{$occurrence->getRequestMethod()}</td>";
          // Request URL
          $tabOccurrencesContentBody .= '<td>';
          if (($occurrence->getRequestMethod() === 'GET') || ($occurrence->getRequestMethod() === '')) {
            $tabOccurrencesContentBody .= "<a href='{$occurrence->getURL()}' target='_blank'>{$occurrence->getURL()}</a>";
          } else {
            $tabOccurrencesContentBody .= $occurrence->getURL();
          }
          $tabOccurrencesContentBody .= '</td>';
          // Exception Message
          /*
          $tabOccurrencesContentBody .= "<td>
<span class='d-inline-block text-truncate' style='max-width: 200px;' title='{$occurrence->getExceptionMessage(true)}'>{$occurrence->getExceptionMessage(true)}</span>
</td>";
          */
          // Code Version
          $tabOccurrencesContentBody .= "<td>{$occurrence->getCodeVersion()}</td>";
          // GET
          $tabOccurrencesContentBody .= "<td class='list-values'>{$helper->listArray($occurrence->getGetArray())}</td>";
          // POST
          $tabOccurrencesContentBody .= "<td class='list-values'>{$helper->listArray($occurrence->getPostArray())}</td>";
          // Query String
          $tabOccurrencesContentBody .= "<td>{$occurrence->getQueryString()}</td>";
          // User IP
          $tabOccurrencesContentBody .= "<td>{$occurrence->getUserIP()}</td>";
          $tabOccurrencesContentBody .= '</tr>';

        }

        $tz = $user->DateTimeZone->getName();
        $tabOccurrencesContent = <<<HTML
<div class="table-responsive table-occurrence">
<table class="table table-hover table-sm ">
<thead class="thead-dark">
<tr>
<th scope="col">Timestamp</th>
<th scope="col">Browser</th>
<th scope="col">OS</th>
<th scope="col">Req. Method</th>
<th scope="col">Request URL</th>
<th scope="col">Code Version</th>
<th scope="col">GET</th>
<th scope="col">POST</th>
<th scope="col">Query String</th>
<th scope="col">User IP</th>
</tr>
</thead>
<tbody>$tabOccurrencesContentBody</tbody>
</table>
</div>
HTML;

      }

      // ----------------------------------- co-occurring items
      $tabCoOccurringItemsContent = '';

      if ((int) $item->firstInChain === 0){
        $query = "SELECT id, project_id, level, language, id_str, type, last_occ, last_timestamp, first_in_chain FROM item WHERE user_id=$user->id and project_id=$projectId and first_in_chain=$item->id";
      } else {
        $query = "SELECT id, project_id, level, language, id_str, type, last_occ, last_timestamp, first_in_chain FROM item WHERE user_id=$user->id and project_id=$projectId and (id=$item->firstInChain or first_in_chain=$item->firstInChain)";
      }

      $coOccurringItems = [];
      /** @noinspection NotOptimalIfConditionsInspection */
      if ($result = $mysqli->query($query) ) {
        while ($obj = $result->fetch_object()) {
          if ($obj !== null) {
            $coOccurringItems[ $obj->id ] = new item($obj);
          }
        }
        $result->close();

        $tabCoOccurringItemsContentBody = '';
        $tabCoOccurringItemsDisabled = true;

        if (count($coOccurringItems) > 0) {
          $tabCoOccurringItemsDisabled = false;

          foreach ($coOccurringItems as $coOccurringItem) {

            $tabCoOccurringItemsContentBody .= <<<HTML
<tr>
<td>{$coOccurringItem->lastOcc}</td>
<td>{$coOccurringItem->getLastTimestampStr('d.m.Y H:i:s', $user->DateTimeZone)}</td>
<td><i class="devicon-{$coOccurringItem->language}-plain colored" title="{$coOccurringItem->language}"></i></td>
<td><a href="{$config->rewrite}project/{$coOccurringItem->projectId}/item/{$coOccurringItem->id}">#{$coOccurringItem->id} {$coOccurringItem->exceptionClass}: {$coOccurringItem->exceptionMessage}</a></td>
<td>{$coOccurringItem->level}</td>
</tr>
HTML;
          }

          $tabCoOccurringItemsContent .= <<<HTML
<div class="table-responsive">
<table class="table table-hover table-sm ">
<thead class="thead-dark">
<tr>
<th scope="col">Total</th>
<th scope="col">Last</th>
<th scope="col"></th>
<th scope="col">Item</th>
<th scope="col">Level</th>
</tr>
</thead>
<tbody>$tabCoOccurringItemsContentBody</tbody>
</table>
</div>
HTML;
        }
      }


        // ----------------------------------- browser / OS
      $tabBrowserContent = '<div class="row">';
      $item->countBrowsers();
      $item->countOs();

      if (count($item->browsers) > 0) {
        $columns = '';
        foreach ($item->browsers as $name => $count) {
          $columns .= "['$name',$count],";
        }

        $tabBrowserContent .= '<div class="col-sm-6"><div id="browser-chart"></div></div>';
        $javascriptContent .= <<<JS
var chartB = c3.generate({
    bindto: '#browser-chart',
    data: {
      columns: [$columns],
      type : 'pie'
    }
});
JS;
      }

      if (count($item->oss) > 0) {
        $columns = '';
        foreach ($item->oss as $name => $count) {
          $columns .= "['$name',$count],";
        }

        $tabBrowserContent .= '<div class="col-sm-6"><div id="os-chart"></div></div>';
        $javascriptContent .= <<<JS
var chartO = c3.generate({
  bindto: '#os-chart',
  data: {
    columns: [$columns],
    type : 'pie'
  }
});
JS;

      }
      $tabBrowserContent .= '</div>';


          // ----------------------------------- IP addresses
      $tabIpAddr = '';
      $item->countUserIP();
      $tabIpAddr .= $helper->listArray($item->userIPs, 'col-sm-2', 'col-sm-10');


      // ----------------------------------- page content
      $content .= <<<HTML
<h4>#{$item->id} <span class="text-danger">{$item->exceptionClass}:</span> {$item->getFirstOcc()->getExceptionMessage(true)}</h4>
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

<div class="ml-auto"><button type="button" class="btn btn-danger" id="btnDeleteItem" data-projectid="{$item->projectId}" data-userid="$user->id" data-itemid="{$item->id}">Delete Item</button></div>
</div>

<hr>

<div class="d-flex flex-row mb-3">
  <div class="p-2">First seen: {$item->getFirstTimestampStr('d.m.Y H:i:s', $user->DateTimeZone)}</div>
  <div class="p-2">Last seen: {$item->getLastTimestampStr('d.m.Y H:i:s', $user->DateTimeZone)}</div>
  <div class="p-2">Occurrences all time: {$item->lastOcc}</div>
  
  <div class="ml-auto"><i class="devicon-{$item->language}-plain colored h1" title="{$item->language}"></i></div>
</div>

<hr>

<nav>
  <div class="nav nav-tabs" id="nav-tab" role="tablist">
    <a class="nav-item nav-link active" id="nav-traceback-tab" data-toggle="tab" href="#nav-traceback" role="tab" aria-controls="nav-traceback" aria-selected="true">{$helper->tabTracebackName($item->type)}</a>
    <a class="nav-item nav-link" id="nav-occurrences-tab" data-toggle="tab" href="#nav-occurrences" role="tab" aria-controls="nav-occurrences" aria-selected="false">Occurrences</a>
    <a class="nav-item nav-link {$helper->disabled($tabCoOccurringItemsDisabled)}" id="nav-cooccurrences-tab" data-toggle="tab" href="#nav-cooccurrences" role="tab" aria-controls="nav-cooccurrences" aria-selected="false">Co-Occurring Items</a>
    <a class="nav-item nav-link" id="nav-browser-tab" data-toggle="tab" href="#nav-browser" role="tab" aria-controls="nav-browser" aria-selected="false">Browser/OS</a>
    <a class="nav-item nav-link" id="nav-ipaddr-tab" data-toggle="tab" href="#nav-ipaddr" role="tab" aria-controls="nav-ipaddr" aria-selected="false">IP Addresses</a>
  </div>
</nav>

<div class="tab-content" id="nav-tabContent">
  <div class="tab-pane pt-3 show active" id="nav-traceback" role="tabpanel" aria-labelledby="nav-traceback-tab">
  $tabTracebackContent
  </div>
  <div class="tab-pane pt-3" id="nav-occurrences" role="tabpanel" aria-labelledby="nav-occurrences-tab">
  $tabOccurrencesContent
  </div>
  <div class="tab-pane pt-3" id="nav-cooccurrences" role="tabpanel" aria-labelledby="nav-cooccurrences-tab">
  $tabCoOccurringItemsContent
  </div>
  <div class="tab-pane pt-3" id="nav-browser" role="tabpanel" aria-labelledby="nav-browser-tab">
  $tabBrowserContent
  </div>
  <div class="tab-pane pt-3" id="nav-ipaddr" role="tabpanel" aria-labelledby="nav-ipaddr-tab">
  $tabIpAddr
  </div>
</div>

<hr>
HTML;


    }
  } else {
    $content .= <<<HTML
<h4 class="text-danger">Item not found</h4>
HTML;

  }
}
