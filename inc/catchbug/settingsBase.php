<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 11.12.18
 * @Time   : 22:30
 */

namespace catchbug;


class settingsBase
{
  private static $data;

  /**
   * settingsBase constructor.
   */
  public function __construct()
  {
    self::$data = (object)[
        'database'                 =>
            (object)[
                'server'   => 'localhost',
                'user'     => 'rollbar',
                'pass'     => '',
                'database' => 'rollbar',
                'port'     => '',
                'socket'   => '',
            ],
        'smtp'                     =>
            (object)[
                'smtp_enable'      => false,
                'smtp_host'        => 'smtp.gmail.com',
                'smtp_port'        => '587',
                'smtp_user'        => '',
                'smtp_password'    => '',
                'smtp_secure'      => 'tls',
                'smtp_from_addr'   => '',
                'smtp_from_name'   => '',
                'smtp_html_enable' => true,
            ],
        'max_occurences'           => 10,
        'default_token_rate_limit' => 1000,
        'rewrite'                  => '/?', // '/' - rewrite is on; '/?' - rewrite is off
        'account_reg'              => false,
        'auto_update'              => true,
        'version'                  => '',
        'latest_version_check'     => 1544496558,
        'latest_version'           => '',
    ];

  }


  /**
   * @return \stdClass
   */
  public function getData(): \stdClass
  {
    return self::$data;
  }

}
