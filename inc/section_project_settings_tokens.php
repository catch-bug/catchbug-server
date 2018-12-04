<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 30.11.18
 * @Time   : 0:57
 */

use rollbug\token;

/** @var \rollbug\user $user */
/** @var \rollbug\helper $helper */
/** @var \rollbug\config $config */


$projectSettingsContent = <<<HTML
<h3>Project Access Tokens</h3>
HTML;

$tokens = [];

$query = "SELECT id, user_id, project_id, token, type, name, disabled, created, last_updated, rate_limit_per, rate_limit_calls FROM token WHERE user_id=$user->id and project_id=$projectId";
if ($result = $mysqli->query($query)) {
  while ($obj = $result->fetch_object()) {
    if ($obj !== null) {
      $tokens[] = new token($obj);
    }
  }


  $tokenTableBodyActive = '';
  $tokenTableBodyDisabled = '';
  /** @var \rollbug\token $token */
  foreach ($tokens as $token){
    $token->setTimeFormat('d.m.Y H:i:s');
    $token->setTimeZone($user->DateTimeZone);
    if ($token->disabled){
      $tokenTableBodyDisabled .= formatTokenTableRow($token, $user);
    } else {
      $tokenTableBodyActive .= formatTokenTableRow($token, $user);
    }
  }

  $projectSettingsContent .= <<<HTML
  <div class="d-flex flex-row mb-3">
  <div class="ml-auto">
<button type='button' class="btn btn-primary btn-sm" id="btnNewToken" data-toggle='modal' data-target='#tokenModal' data-project_id="$projectId" data-user_id="$user->id">Create new token</button>
</div>
</div>
HTML;


  if ($tokenTableBodyActive !== '') {
    $projectSettingsContent .= <<<HTML
    <h4>Active tokens</h4>
<div class="table-responsive">
<table class="table table-hover table-sm ">
<thead class="thead-dark">
<tr>
<th scope="col">Name</th>
<th scope="col">Token</th>
<th scope="col">Created</th>
<th scope="col"></th>
</tr>
</thead>
<tbody>$tokenTableBodyActive</tbody>
</table>
HTML;
  }

  if ($tokenTableBodyDisabled !== '') {
    $projectSettingsContent .= <<<HTML
    <h4>Disabled tokens</h4>
<div class="table-responsive">
<table class="table table-hover table-sm ">
<thead class="thead-dark">
<tr>
<th scope="col">Name</th>
<th scope="col">Token</th>
<th scope="col">Created</th>
<th scope="col"></th>
</tr>
</thead>
<tbody>$tokenTableBodyDisabled</tbody>
</table>
HTML;
  }

  $modalWindow = <<<HTML
<div class="modal" id="tokenModal" tabindex="-1" role="dialog" aria-labelledby="tokenModalTitle" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tokenModalTitle">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <form id="formEditToken">
      <div class="form-group tokenEdit">
        <div id="lastUpdated">Last Updated: <span id="tokenModalLastUpdated"></span></div>
      </div>
      
      <fieldset class="form-group">
      <legend class="">Access Scopes</legend>
            <div class="form-row">
      <div class="form-group col">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="type[]" value="post_client_item" id="cbPostClientItem">
        <label class="form-check-label" for="cbPostClientItem">post_client_item - <em>Allows POST client-side items (<code>platform</code> is <code>android</code>, <code>browser</code>, <code>client</code>, <code>flash</code>, or <code>ios</code>)</em></label>
      </div>
      </div>
      </div>
      
            <div class="form-row">
      <div class="form-group col">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="type[]" value="post_server_item" id="cbPostServerItem">
        <label class="form-check-label" for="cbPostServerItem">post_server_item - <em>Allows POST deploys and server-side items (other platforms)</em></label>
      </div>
      </div>
      </div>
            <div class="form-row">
      <div class="form-group col">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="type[]" value="read" id="cbRead">
        <label class="form-check-label" for="cbRead">read - <em>Allows all GET calls</em></label>
      </div>
      </div>
      </div>
            <div class="form-row">
      <div class="form-group col">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="type[]" value="write" id="cbWrite">
        <label class="form-check-label" for="cbWrite">write - <em>Allows all write calls, except POSTing items or deploys</em></label>
      </div>
      </div>
      </div>
      </fieldset> 
      
      <fieldset class="form-group">
      <legend class="">Rate Limit</legend>
      <div class="form-row">      
      <div class="form-group col-md-6">      
      <label for="selLimitPer" class="limitNoDefault invisible">Per</label>
      <select id="selLimitPer" name="limit_per" class="form-control custom-select" data-max_rate="{$config->default_token_rate_limit}">
        <option value="Default">Default</option>
        <option value="1 minute">1 minute</option>
        <option value="5 minutes">5 minutes</option>
        <option value="30 minutes">30 minutes</option>
        <option value="1 hour">1 hour</option>
        <option value="1 day">1 day</option>
        <option value="1 week">1 week</option>
        <option value="1 month">1 month</option>
      </select>
      <small class="form-text text-muted limitDefault">Default rate: {$config->default_token_rate_limit} calls per minute</small>
      </div>

      <div class="form-group col-md-6 limitNoDefault invisible">
      <label for="inpLimitCalls" class="limitNoDefault invisible">Calls</label>
        <input type="text" class="form-control" id="inpLimitCalls" name="limit_calls" data-rule-min="1" data-rule-digits="true">
        <small class="form-text text-muted">You can set max rate <span id="spTokenRateLimit"></span> calls per <span id="spTokenRatePer"></span></small>
      </div>           
      </div>
      </fieldset>
      
      <fieldset class="form-group">
      <legend class="">Name</legend>
      <div class="form-group row">
      <div class="col">
        <input type="text" class="form-control" id="inpName" name="name">
      </div>  
      </div>
      </fieldset>
      
      <fieldset class="form-group">
      <legend class="">Disable Token</legend>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="disabled" value="on" id="cbDisableToken">
        <label class="form-check-label" for="cbDisableToken">Disable this token</label>
      </div>
      </fieldset>
      
      <fieldset class="form-group text-danger" id="tokenModalError"></fieldset>
        
      <input hidden type="text" name="tokenid" id="inpTokenId">  
      <input hidden type="text" name="userid" id="inpUserId">  
      <input hidden type="text" name="projectid" id="inpProjectId">  
      <input hidden type="text" name="section" value="tokens">  
      </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger mr-auto tokenEdit" id="btnDeleteToken" data-token-id="{$token->id}">Delete This Token</button>
      
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="formEditTokenSubmit">Save changes</button>
      </div>
    </div>
  </div>
</div>
HTML;


}



function formatTokenTableRow (\rollbug\token $token, \rollbug\user $user): string
{
  $row = '';
  $row .= '<tr>';
  $row .= "<td>$token->name</td>";
  $row .= "<td>$token->token<br><small>$token->types</small></td>";
  $row .= "<td>{$token->getCreatedStr('d.m.Y H:i:s', $user->DateTimeZone)}</td>";
  $row .= "<td><button type='button' class='btn btn-outline-success btn-sm btn-edit-token' data-toggle='modal' data-target='#tokenModal' data-token='{$token}'>Edit</button></td>";
  $row .= '</tr>';

  return $row;
}

