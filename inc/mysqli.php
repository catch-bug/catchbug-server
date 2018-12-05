<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 19.11.18
 * @Time   : 16:00
 */

$mysqli = new mysqli($config->database->server, $config->database->user, $config->database->pass, $config->database->database, $config->database->port, $config->database->socket);

if ($mysqli->connect_error){
  die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8');
$mysqli->query('SET time_zone=\'+00:00\';');
date_default_timezone_set('UTC');

