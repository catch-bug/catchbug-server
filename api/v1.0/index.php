<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 7.11.18
 * @Time   : 18:38
 */

error_reporting(E_ALL);

// get the HTTP method, path and body of the request
$path = $_GET['path'] ?? $_GET['path'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_GET['path'],'/'));

$input = file_get_contents('php://input');

$payload = json_decode($_POST['payload'] ?? $_GET['payload'] ?? $input ?? '',false);

$access_token = $_GET['access_token'] ?? $payload->access_token ?? '';

require_once __DIR__ . '/../../inc/config.php';
$config = new config();

switch ($request[0]){
  case 'status':
    switch ($request[1]){
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

