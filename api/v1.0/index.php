<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 7.11.18
 * @Time   : 18:38
 */

error_reporting(E_ALL);

use catchbug\config;

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
  // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
  // you want to allow, and if so:
  header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
  header('Access-Control-Allow-Credentials: true');
  header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

  if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])){
    // may also be using PUT, PATCH, HEAD etc
    header('Access-Control-Allow-Methods: GET, POST, DELETE, PATCH, OPTIONS');
  }

  if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
    header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
  }

  exit(0);
}

// get the HTTP method, path and body of the request
$path = $_GET['path'] ?? $_GET['path'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_GET['path'],'/'));

$input = file_get_contents('php://input');

$payload = json_decode($_POST['payload'] ?? $_GET['payload'] ?? $input ?? '',false, 512, JSON_BIGINT_AS_STRING);

$access_token = $_GET['access_token'] ?? $payload->access_token ?? '';

require_once __DIR__ . '/../../vendor/autoload.php';

$config = new config();

switch ($request[0]) {
  case 'status':
    switch ($request[1]) {
      case 'ping':
        include __DIR__ . '/status/ping.php';
        break;
    }
    break;

  case 'item':
    include __DIR__ . '/item.php';
    break;


  default:
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(404);
    echo '{"err": 1, "message": "not found"}';
}


ob_start();

echo "\n---\nmethod\n";
var_dump($method);
echo "\n\npath\n";
var_dump($path);
echo "\n\nrequest\n";
var_dump($request);
echo "\n\ninput\n";
var_dump($input);
echo "\n\n_GET\n";
var_dump($_GET);
echo "\n\n_POST\n";
var_dump($_POST);
echo "\n\npayload\n";
var_dump($payload);
echo "\n---------------------------------------------------------------------------------------------\n";
$data = ob_get_clean();
$fp = fopen(__DIR__ . "/textfile.txt", 'ab');
fwrite($fp, $data);
fclose($fp);

