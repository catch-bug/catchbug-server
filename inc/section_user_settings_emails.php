<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 10.12.18
 * @Time   : 0:42
 */


/** @var \rollbug\user $user */
/** @var \rollbug\helper $helper */


$emailsTableBody = '';
$optionsEmail = '';

/** @var \rollbug\userEmail $email */
foreach ($user->emails as $email){
  $emailsTableBody .= '<tr>';
  $emailsTableBody .= "<td>{$email->email}</td>";
  $emailsTableBody .= "<td>{$helper->ifEcho($email->main,'main')}</td>";
  $emailsTableBody .= "<td class='text-right'>{$email->getVerifyLink($user->id)}<button type='button' class='btn btn-sm btn-danger btn-delete-email' data-id='$email->id' data-email='$email->email' data-user-id='$user->id'>Delete</button></td>";
  $emailsTableBody .= '</tr>';

  $optionsEmail .= "<option value='{$email->id}' {$helper->ifEcho($email->main,' selected')}>";
  $optionsEmail .= $email->email;
  $optionsEmail .= '</option>';

}


$userSettingsContent= <<<HTML
<h3>Email settings</h3>
<div class="card mb-3">
  <div class="card-header">Email Addresses</div>
  <div class="card-body">
  <div class="table-responsive">
   <table class="table table-borderless table-hover">
   $emailsTableBody
   </table>
  </div>
  
  <hr>
  <form id="form_user_settings_add_email">
  <fieldset class="form-group">
   <legend class="">Add new email</legend>
   
  <div class="form-group">
    <label for="inpEmail">Email address</label>
    <input type="text" class="form-control" id="inpEmail" name="email" placeholder="Enter email" data-rule-required="true" data-msg-required="Please enter email" data-rule-email="true" data-msg-email="Please enter a valid email address.">
  </div>
  
  </fieldset>
  
    <input type="text" name="section" value="add_email" hidden>
    <input type="text" name="user_id" value="{$user->id}" hidden>
  <button type="submit" class="btn btn-primary float-right">Add new email</button>
  </form>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header">Main Email Address</div>
  <div class="card-body">
  <em>Main email is used for password recovery, system messages related to user, user communication, etc.</em>
  <form class="form-inline">
  <label class="my-1 mr-2" for="selMailEmail">Main email: </label>
  <select class="custom-select my-1 mr-sm-2" id="selMailEmail" name="main_email">$optionsEmail</select>
  
     <input type="text" name="section" value="set_main_email" hidden>
    <input type="text" name="user_id" value="{$user->id}" hidden>
  </form>
  </div>
</div>
HTML;
