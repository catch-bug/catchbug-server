<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 11.12.18
 * @Time   : 0:10
 */

use rollbug\config;

/** @var \rollbug\helper $helper */
unset($config);
try {
  $config = new config(true, true);
} catch (Exception $e) {
  die($e->getMessage());
}

$rewriteOn = $config->rewrite === '/';

$dirs = glob_recursive('*', 0);

$dirsWritable = true;
$dirsWithWrongPermission = '';
foreach ($dirs as $dir) {
  if (!is_writable($dir)) {
    $dirsWritable = false;
    $dirsWithWrongPermission .= $dir . "\n";
  }
}

function glob_recursive(string $pattern, int $flags = 0):array
{
  $files = glob($pattern, $flags);

  foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
  {
    /** @noinspection SlowArrayOperationsInLoopInspection */
    $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
  }

  return $files;
}
/*
echo '<pre>';
var_dump( $config->getData());
echo '</pre>';
*/
$systemSettingsContent= <<<HTML
<h3>General settings</h3>

<form id="formSettingsGeneral">
	<div class="form-group  row">
		<label for="inpMaxOccurrences" class="col-sm-4 col-form-label">Max occurrences</label>
		<div class="col-sm-8">
		<input type="number" name="config[max_occurrences]" id="inpMaxOccurrences" class="form-control" value="{$config->max_occurences}" data-rule-required="true" data-rule-min="1" data-rule-digits="true">
		</div>
		<div class="col-sm-12">
		<small class="text-muted">How much max occurrences is write for item</small>
		</div>
	</div>
	
	<div class="form-group  row">
		<label for="inpDefaultTokenRateLimit" class="col-sm-4 col-form-label">Default token rate limit</label>
		<div class="col-sm-8">
		<input type="number" name="config[default_token_rate_limit]" id="inpDefaultTokenRateLimit" class="form-control" value="{$config->default_token_rate_limit}"  data-rule-required="true" data-rule-min="1" data-rule-digits="true">
		</div>
		<div class="col-sm-12">
		<small class="text-muted">How much max items can token write per minute</small>
		</div>
	</div>
		
	<div class="form-group row">
		<label for="" class="col-sm-4 col-form-label">Rewrite in web interface</label>
		<div class="col-sm-8">
				
		<div class="form-check form-check-inline col-sm-12">
      <input class="form-check-input" type="radio" name="config[rewrite]" id="radioRewriteOn" value="on" {$helper->checked($rewriteOn)}>
      <label class="form-check-label" for="radioRewriteOn">Rewrite on</label>
    </div>    
    <div class="form-check form-check-inline col-sm-12">
      <input class="form-check-input" type="radio" name="config[rewrite]" id="radioRewriteOff" value="off" {$helper->checked(!$rewriteOn)}>
      <label class="form-check-label" for="radioRewriteOff">Rewrite off</label>
    </div>
    
		</div>
		<div class="col-sm-12">
		<small class="text-muted">Addresses in web interface. Rewrite on: <code>your.domain/system</code> Rewrite off: <code>your.domain/?system</code> You must properly set rewrite on web server too.</small>
		</div>
	</div>
	
	<div class="form-group row">
		<label for="" class="col-sm-4 col-form-label">Account registration</label>
		<div class="col-sm-8">
		
		<div class="form-check form-check-inline col-sm-12">
      <input class="form-check-input" type="radio" name="config[account_reg]" id="radioAccountRegOn" value="on" {$helper->checked($config->account_reg)}>
      <label class="form-check-label" for="radioAccountRegOn">Allow <small class="text-muted">Users can create accounts by themselves.</small></label>
    </div>    
    <div class="form-check form-check-inline col-sm-12">
      <input class="form-check-input" type="radio" name="config[account_reg]" id="radioAccountRegOff" value="off" {$helper->checked(!$config->account_reg)}>
      <label class="form-check-label" for="radioAccountRegOff">Deny <small class="text-muted">Only administrators can create accounts.</small></label>
    </div>	
  
		</div>
		<div class="col-sm-12">
		<small class="text-muted"></small>
		</div>
	</div>
	
	<div class="form-group row">
		<label for="cbAutoUpdate" class="col-sm-4 col-form-label {$helper->ifEcho(!$dirsWritable,'text-muted')}">Auto update</label>
		<div class="col-sm-8">	
	    <div class="form-check">
      <input class="form-check-input position-static" type="checkbox" id="cbAutoUpdate" value="on" name="config[auto_update]" {$helper->checked($config->auto_update && $dirsWritable)} {$helper->disabled(!$dirsWritable)}>
      </div>
		</div>
		
		<div class="col-sm-12">
		<small class="text-muted">Automatic one click update to new version. {$helper->ifEcho(!$dirsWritable, '<em class="text-danger">There is some files with <a data-toggle="collapse" href="#collapseFiles">wrong permissions</a>. Auto update will not work</em>')}</small>
		</div>
		
    <div class="collapse col-sm-12" id="collapseFiles">
      <div class="card card-body">
      <pre>$dirsWithWrongPermission</pre>
      </div>
    </div>		
	</div>
	
	<hr>
	
	<div class="form-group row">
    <label for="" class="col-sm-4 col-form-label font-weight-bold">SMTP configuration</label>
	</div>
	<div class="form-group  row">
		<label for="inpSmtpHost" class="col-sm-4 col-form-label">SMTP host</label>
		<div class="col-sm-8">
		<input type="text" name="config[smtp][smtp_host]" id="inpSmtpHost" class="form-control" value="{$config->smtp->smtp_host}">
		</div>
	</div>
	
	<div class="form-group  row">
		<label for="inpSmtpPort" class="col-sm-4 col-form-label">SMTP port</label>
		<div class="col-sm-8">
		<input type="text" name="config[smtp][smtp_port]" id="inpSmtpPort" class="form-control" value="{$config->smtp->smtp_port}" data-rule-min="1" data-rule-digits="true">
		</div>
	</div>
	
	<div class="form-group  row">
		<label for="inpSmtpUser" class="col-sm-4 col-form-label">SMTP user</label>
		<div class="col-sm-8">
		<input type="text" name="config[smtp][smtp_user]" id="inpSmtpUser" class="form-control" value="{$config->smtp->smtp_user}">
		</div>
	</div>
	
	<div class="form-group  row">
		<label for="inpSmtpPassword" class="col-sm-4 col-form-label">SMTP password</label>
		<div class="col-sm-8">
		<input type="password" name="config[smtp][smtp_password]" id="inpSmtpPassword" class="form-control" value="{$config->smtp->smtp_password}">
		</div>
	</div>
	
	<div class="form-group row">
		<label for="" class="col-sm-4 col-form-label">SMTP encryption</label>
		<div class="col-sm-8">
		
		<div class="form-check form-check-inline col-sm-12">
      <input class="form-check-input" type="radio" name="config[smtp][smtp_secure]" id="radioSmtpSecureSsl" value="ssl" {$helper->checked($config->smtp->smtp_secure === 'ssl')}>
      <label class="form-check-label" for="radioSmtpSecureSsl">SMTPS <small class="text-muted">ssl encryption</small></label>
    </div>    
    <div class="form-check form-check-inline col-sm-12">
      <input class="form-check-input" type="radio" name="config[smtp][smtp_secure]" id="radioSmtpSecureTls" value="tls" {$helper->checked($config->smtp->smtp_secure === 'tls')}>
      <label class="form-check-label" for="radioSmtpSecureTls">SMTP + STARTTLS <small class="text-muted">start encryption with STARTTLS</small></label>
    </div>	
  
		</div>
		<div class="col-sm-12">
		<small class="text-muted"></small>
		</div>
	</div>
	
	<div class="form-group  row">
		<label for="inpSmtpFromAddr" class="col-sm-4 col-form-label">FROM address</label>
		<div class="col-sm-8">
		<input type="text" name="config[smtp][smtp_from_addr]" id="inpSmtpFromAddr" class="form-control" value="{$config->smtp->smtp_from_addr}" data-rule-email="true">
		</div>
	</div>
	
	<div class="form-group  row">
		<label for="inpSmtpFromName" class="col-sm-4 col-form-label">FROM name</label>
		<div class="col-sm-8">
		<input type="text" name="config[smtp][smtp_from_name]" id="inpSmtpFromName" class="form-control" value="{$config->smtp->smtp_from_name}">
		</div>
	</div>
	
	<div class="form-group row">
		<label for="cbSmtpHtmlEnable" class="col-sm-4 col-form-label">Enable HLML emails</label>
		<div class="col-sm-8">	
	    <div class="form-check">
      <input class="form-check-input position-static" type="checkbox" id="cbSmtpHtmlEnable" value="on" name="config[smtp][smtp_html_enable]" {$helper->checked($config->smtp->smtp_html_enable)}>
      </div>
		</div>
	</div>
	
	
	<div class="form-group row justify-content-end">	
		<label for="inpTestMail" class="col-form-label">Send test mail to: </label>
		<div class="col-sm-5">
	  <div class="input-group">	
		  <input type="text" name="config[smtp][test_mail]" id="inpTestMail" class="form-control" value="" data-rule-email="true">
		  <div class="input-group-append">
        <button class="btn btn-outline-secondary" type="button" id="btnSendTestMail">Send</button>
      </div>
     </div>
		</div>		
	</div>
	
	<div class="form-group row">
	  <div class="col" id="divErrorTestMail"></div>
	</div>

	<hr>
	<button type="submit" class="btn btn-primary float-right">Save Settings</button>
</form>


HTML;
