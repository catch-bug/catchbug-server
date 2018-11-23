<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 19.11.18
 * @Time   : 16:02
 */

class config
{

  public $db_server = 'localhost';

  public $db_user = 'rollbar';

  public $db_pass = 'prdel';

  public $db_database = 'rollbar';
  public $db_port = '5719';
  public $db_socket = '/tmp/mysql_sandbox5719.sock';


  public $max_occurences = 10;

  public function __construct()
  {

  }
}
