<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 21.11.18
 * @Time   : 17:34
 */

use rollbug\user;

session_start();

/**
 *
 * @var $ajax_token string ajax security token
 */
if(!isset($_SESSION['ajax_token'])) {
  try {
    $ajax_token = md5(random_bytes(255));
  } catch (Exception $e) {
  }
  $_SESSION['ajax_token'] = $ajax_token;
} else {
  $ajax_token = $_SESSION['ajax_token'];
}

$content = '';
$title = '';

$section = strtok($_SERVER['QUERY_STRING'], '/');
$active = new active();

if (isset($_SESSION['user_id'])){
  require_once __DIR__ . '/inc/config.php';
  $config = new config();

  require_once __DIR__ . '/inc/mysqli.php';
  require_once __DIR__ . '/inc/user.php';

  $user = new user($_SESSION['user_id'], $mysqli);

  $projectId = null;

  switch ($section){
    case 'user':
      include __DIR__ . '/inc/section_user.php';
      break;

    case 'project':
      $projectId = (integer) strtok('/');

      $projectSection = strtok('/');
      $projectSection = $projectSection === false ? 'items' : $projectSection;

      $projectName = $projectId !== 0 ? $user->getProject($projectId)->getName() : 'All projects';

      $content .= <<<HTML
<h1>{$projectName}</h1>

<ul class="nav justify-content-center subnav">
  <li class="nav-item {$active->checkActive($projectSection, 'items')}">
    <a class="nav-link" href="/?project/$projectId/items">Items</a>
  </li>
HTML;

      if ($projectId !== 0) {
        $content .= <<<HTML
  <li class="nav-item {$active->checkActive($projectSection, 'settings')}">
    <a class="nav-link" href="/?project/$projectId/settings">Settings</a>
  </li> 
HTML;
      }

      $content .= <<<HTML
</ul>
<div class="pt-4"></div>
HTML;

      switch ($projectSection){
        case 'items':
          include __DIR__ . '/inc/section_project_items.php';
          break;

        case 'item':
          $projectItem = (integer) strtok('/');
          include __DIR__ . '/inc/section_project_item.php';
          break;

        case 'settings':
          $projectSectionMenu = strtok('/');
          $projectSectionMenu = $projectSectionMenu === false ? 'general' : $projectSectionMenu;

          break;
      }
      break;

    case 'newproject':

      break;
  }

} else {

}




echo <<<HTML
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/custom.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <title>$title</title>

  </head>
  <body>
  
<div id="errorModal" class="modal fade">
	<div class="modal-dialog modal-confirm">
		<div class="modal-content">
			<div class="modal-header justify-content-center">
				<div class="icon-box">
					<i class="material-icons">&#xE5CD;</i>
				</div>				
				<h4 class="modal-title">Sorry!</h4>	
			</div>
			<div class="modal-body">
				<p class="text-center" id="errorModalText">Your transaction has failed. Please go back and try again.</p>
			</div>
			<div class="modal-footer">
				<button class="btn btn-danger btn-block" data-dismiss="modal">OK</button>
			</div>
		</div>
	</div>
</div>
HTML;
// ----------------------------------- navbar
echo <<<HTML
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
      <a class="navbar-brand" href="/">Fixed navbar</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarCollapse">
HTML;

if (isset($_SESSION['user_id'])) {
  echo <<<HTML
        <ul class="navbar-nav mr-auto">
          <li class="nav-item {$active->checkActive($section, 'dashboard')}">
            <a class="nav-link" href="/?dashboard">Dashboard</a>
          </li>
          <li class="nav-item dropdown {$active->checkActive($section, 'project')}">
            <a class="nav-link dropdown-toggle" href="#" id="navbarProjects" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Projects</a>
            <div class="dropdown-menu" aria-labelledby="navbarProjects">
              <a class="dropdown-item {$active->checkActive($projectId, 0)}" href="/?project/">All projects</a>

HTML;

  /** @var \rollbug\project $project */
  foreach ($user->projects as $project){
    echo <<<HTML
<a class="dropdown-item {$active->checkActive($projectId, $project->getId())}" href="/?project/{$project->getId()}/items" data-id="{$project->getId()}">{$project->getName()}</a>
HTML;

  }

  echo <<<HTML
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" id="navNewProject" href="/?newproject">New project</a>
            </div>
          </li>
        </ul>
HTML;
}

// user logout
if (isset($_SESSION['user_id'])){
  echo <<<HTML
<ul class="nav navbar-nav navbar-right ml-auto">
  <li class="nav-item">
    <a class="nav-link" href="/?user/{$user->name}" data-id="{$user->getId()}" id="navUser">{$user->name}</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="#" id="navLogout">Log out</a>
  </li>
</ul>
HTML;

} else {
  // user login and register forms
  echo <<<HTML
<ul class="nav navbar-nav navbar-right ml-auto">			
			<li class="nav-item">
				<a data-toggle="dropdown" class="nav-link dropdown-toggle" href="#">Login</a>
				<ul class="dropdown-menu form-wrapper">					
					<li>
						<form id="formLogin">
							<div class="form-group">
								<input type="text" name="username" class="form-control" placeholder="Username" required="required">
							</div>
							<div class="form-group">
								<input type="password" name="password" class="form-control" placeholder="Password" required="required">
							</div>
							<input type="submit" class="btn btn-primary btn-block" value="Login">
							<div class="form-footer">
								<a href="#">Forgot Your password?</a>
							</div>
							
							<div class="or-seperator"><b>or</b></div>					
						
							<p class="hint-text">Sign in with your social media account</p>
							<div class="form-group social-btn clearfix">
								<a href="#" class="btn btn-primary pull-left"><i class="fa fa-facebook"></i> Facebook</a>
								<a href="#" class="btn btn-info pull-right"><i class="fa fa-twitter"></i> Twitter</a>
							</div>
							
						</form>
					</li>
				</ul>
			</li>
			<li class="nav-item">
				<a href="#" data-toggle="dropdown" class="btn btn-primary dropdown-toggle get-started-btn mt-1 mb-1">Sign up</a>
				<ul class="dropdown-menu form-wrapper">					
					<li>
						<form id="formRegister">
							<p class="hint-text">Fill in this form to create your account!</p>
							<div class="form-group">
								<input type="text" name="username" class="form-control" placeholder="Username" required="required">
							</div>
							<div class="form-group">
								<input type="password" name="password" class="form-control" placeholder="Password" required="required">
							</div>
							<div class="form-group">
								<input type="password" name="passwordConfirm" class="form-control" placeholder="Confirm Password" required="required">
							</div>
							<div class="form-group">
								<label class="checkbox-inline"><input type="checkbox" name="consent" required="required"> I accept the <a href="#">Terms &amp; Conditions</a></label>
							</div>
							<input type="submit" class="btn btn-primary btn-block" value="Sign up">
						</form>
					</li>
				</ul>
			</li>
		</ul>
HTML;
}

echo <<<HTML
       </div>
    </nav>
HTML;
// ----------------------------------- content

echo '<main role="main" class="container" id="content">' . $content . '</main>';

echo <<<HTML
<script type="text/javascript">
  var AJAX_TOKEN = '$_SESSION[ajax_token]';
</script>
    <script src="/js/jquery-3.3.1.min.js"></script>
    <script src="/js/popper.min.js"></script>
    <script src="/js/bootstrap.bundle.min.js"></script>
    <script src="/js/index.js"></script>
 
  <div id="loading" style="display: none;"></div>
  </body>
</html>
HTML;



class active
{
  public function checkActive($section, $item)
  {
    return $section === $item ? ' active' : '';
  }
}
