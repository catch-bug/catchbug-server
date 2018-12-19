<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 13.12.18
 * @Time   : 15:17
 */

namespace catchbug;

use PHPMailer\PHPMailer\PHPMailer;
use SimpleHtmlToText\Parser;

class mailer extends PHPMailer
{

  private $isHTML = false;

  /**
   * mailer constructor.
   *
   * @param \catchbug\config|null $config
   * @param bool                  $exceptions
   *
   * @throws \PHPMailer\PHPMailer\Exception
   */
  public function __construct(config $config=null, bool $exceptions=false)
  {
    parent::__construct($exceptions);
    $this->SMTPOptions = array (
        'ssl' => array (
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $this->XMailer = 'rollBug https://github.com/catch-bug/catchbug-server/ powered by PHPMailer';

    if($config !== null){
      $this->setConfig($config);
    }
  }

  /**
   * @param bool $isHtml
   */
  public function isHTML($isHtml = true):void
  {
    parent::isHTML($isHtml);
    $this->isHTML = $isHtml;
  }

  /**
   * @param \catchbug\config $config
   *
   * @throws \PHPMailer\PHPMailer\Exception
   */
  public function setConfig(config $config):void
  {
    // Server settings
    if ($config->smtp->smtp_enable) {
      $this->isSMTP();
      $this->Host = gethostbyname($config->smtp->smtp_host);
      $this->SMTPAuth = ($config->smtp->smtp_user !== '') && ($config->smtp->smtp_password !== '');
      $this->Username = $config->smtp->smtp_user;
      $this->Password = $config->smtp->smtp_password;
      $this->SMTPSecure = $config->smtp->smtp_secure;
      $this->Port = $config->smtp->smtp_port;
    } else {
      $this->isMail();
    }

    //Recipients
    $this->setFrom($config->smtp->smtp_from_addr, $config->smtp->smtp_from_name);

    //Content
    $this->isHTML($config->smtp->smtp_html_enable);
  }

  /**
   * @param string $body
   */
  public function setBody(string $body):void
  {
    if ($this->isHTML) {
      $this->isHTML(true);
      $this->Body = $body;
      $this->AltBody = (new Parser())->parseString($body);
    } else {
      $this->isHTML(false);
      $this->Body = (new Parser())->parseString($body);
    }
  }

}
