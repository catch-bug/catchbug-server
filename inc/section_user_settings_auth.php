<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 9.12.18
 * @Time   : 23:34
 */


/** @var \catchbug\user $user */
/** @var \catchbug\helper $helper */

$userSettingsContent= <<<HTML
<h3>Authentication settings</h3>
<div class="card mb-3">
  <div class="card-header">Change password</div>
  <div class="card-body">
    <form id="form_user_settings_change_password">
    <div class="form-group">
    <label for="inpOldPassword">Old Password</label>
    <input type="password" class="form-control" id="inpOldPassword" name="old_password" placeholder="Old Password" data-rule-required="true" data-msg-required="Please enter password">
    </div>
    
    <div class="form-group">
    <label for="inpNewPassword">New Password</label>
    <input type="password" class="form-control" id="inpNewPassword" name="new_password" placeholder="New Password" data-rule-required="true" data-msg-required="Please enter new password" data-rule-minlength="5" data-msg-minlength="Min 5 char">
    </div>
    
    <div class="form-group">
    <label for="inpConfirmNewPassword">Confirm New Password</label>
    <input type="password" class="form-control" id="inpConfirmNewPassword" name="confirm_new_password" placeholder="Confirm New Password" data-rule-required="true" data-msg-required="Please confirm new password" data-rule-equalTo="#inpNewPassword" data-msg-equalTo="Passwords not match">
    </div>
    
    <input type="text" name="section" value="auth_ch_pwd" hidden>
    <input type="text" name="user_id" value="{$user->id}" hidden>
    
    <button type="submit" class="btn btn-primary float-right">Save</button>
    </form>
  </div>
</div>
HTML;
