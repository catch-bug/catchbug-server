<?php
/**
 *
 * Copyright (c) 2016. Mira
 * http://z-web.eu
 * all rights reserved
 * Last update: 15.9.16 0:07
 *
 */

/**
 * @Project: inyw_web
 * @User: mira
 * @Date: 14.9.16
 * @Time: 23:49
 */
session_start();

$_SESSION['user_id'] = '';
unset ($_SESSION['user_id']);

$_COOKIE['auth'] = '';
setcookie ('auth', '', time() - 3600,'/');

$base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
header("Location: $base_url");
