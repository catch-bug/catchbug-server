<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 19.11.18
 * @Time   : 16:00
 */

$mysqli = new mysqli($config->db_server, $config->db_user, $config->db_pass, $config->db_database, $config->db_port, $config->db_socket);

if ($mysqli->connect_error){
  die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8');

