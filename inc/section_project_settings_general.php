<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 30.11.18
 * @Time   : 0:29
 */

/** @var \rollbug\user $user */
/** @var \rollbug\helper $helper */

$projectSettingsContent = <<<HTML
<h3>General Project Settings</h3>
<form id="formProjectSettings">
  <div class="form-group">
    <label for="formProjectSettingsName">Project name</label>
    <input type="text" class="form-control" id="formProjectSettingsName" placeholder="Enter project name" name="name" value="{$user->getProject($projectId)->getName()}" maxlength="50" data-rule-required="true" data-msg-required="Please enter project name" data-rule-maxlength="50" data-msg-maxlength="Max 50 char">
    <small class="form-text text-muted">Up to 50 char.</small>
  </div>
  
    <div class="form-group">
    <label for="formProjectSettingsDesc">Project description</label>
    <textarea class="form-control" id="formProjectSettingsDesc" placeholder="Enter project description" maxlength="250" name="desc"  data-rule-maxlength="250" data-msg-maxlength="Max 250 char">{$user->getProject($projectId)->getDescription()}</textarea>
    <small class="form-text text-muted">Up to 250 char.</small>
  </div>

  <input type="text" name="section" value="general" hidden>
  <input type="text" name="userid" value="{$user->id}" hidden>
  <input type="text" name="projectid" value="$projectId" hidden>
  
  <button type="submit" class="btn btn-primary float-right">Save</button>
</form>
HTML;
