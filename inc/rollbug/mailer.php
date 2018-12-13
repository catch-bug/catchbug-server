<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 13.12.18
 * @Time   : 15:17
 */

namespace rollbug;

use PHPMailer\PHPMailer\PHPMailer;
use SimpleHtmlToText\Parser;

class mailer extends PHPMailer
{

  private $isHTML = false;

  /**
   * mailer constructor.
   *
   * @param \rollbug\config|null $config
   * @param bool                 $exceptions
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
    $this->XMailer = 'rollBug https://github.com/rollbug/rollbug-server/ powered by PHPMailer';
    $this->isSMTP();

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
   * @param \rollbug\config $config
   *
   * @throws \PHPMailer\PHPMailer\Exception
   */
  public function setConfig(config $config):void
  {
    // todo Server settings
    $this->Host = gethostbyname('z-web.eu');
    $this->SMTPAuth = true;
    $this->Username = 'github@z-web.eu';
    $this->Password = 'prdelvod';
    $this->SMTPSecure = 'tls';
    $this->Port = 587;

    //Recipients
    $this->setFrom('github@z-web.eu', 'Mailer');

    //Content
    $this->isHTML(true);
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
