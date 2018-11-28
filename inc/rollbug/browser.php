<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 26.11.18
 * @Time   : 7:41
 */

namespace rollbug;


class browser extends \Sinergi\BrowserDetector\Browser
{
  private $logoDirs = array(
      'unknown'                  => 'fake',
      'Vivaldi'                  => 'vivaldi',
      'Opera'                    => 'opera',
      'Opera Mini'               => 'opera-mini',
      'WebTV'                    => 'web',
      'Internet Explorer'        => 'internet-explorer_9-11',
      'Pocket Internet Explorer' => 'internet-explorer_7-8',
      'Konqueror'                => 'konqueror_4',
      'iCab'                     => 'icab-mobile',
      'OmniWeb'                  => 'omniweb_6',
      'Firebird'                 => 'firebird',
      'Firefox'                  => 'firefox',
      'SeaMonkey'                => 'seamonkey',
      'Iceweasel'                => 'iceweasel',
      'Shiretoko'                => 'fake',
      'Mozilla'                  => 'firefox_1',
      'Amaya'                    => 'fake',
      'Lynx'                     => 'lynx',
      'wkhtmltopdf'              => 'fake',
      'Safari'                   => 'safari',
      'SamsungBrowser'           => 'samsung-internet',
      'Chrome'                   => 'chrome',
      'Navigator'                => 'netscape_9',
      'GoogleBot'                => 'fake',
      'Yahoo! Slurp'             => 'fake',
      'W3C Validator'            => 'fake',
      'BlackBerry'               => 'fake',
      'IceCat'                   => 'icecat',
      'Nokia S60 OSS Browser'    => 'fake',
      'Nokia Browser'            => 'fake',
      'MSN Browser'              => 'fake',
      'MSN Bot'                  => 'fake',
      'Netscape Navigator'       => 'netscape_9',
      'Galeon'                   => 'fake',
      'NetPositive'              => 'fake',
      'Phoenix'                  => 'phoenix-firebird',
      'Google Search Appliance'  => 'fake',
      'Yandex'                   => 'yandex',
      'Edge'                     => 'edge',
      'Dragon'                   => 'fake'
  );

  const IMG_DIR = '/img/browser_logos/';


  public function getImg(int $size = 32): string
  {
    $fileName = $this->logoDirs[ $this->getName() ];
    return <<<HTML
<img src="{$this->getImgDir()}{$fileName}/{$fileName}_{$size}x{$size}.png" title="{$this->getName()} {$this->getVersion()}">
HTML;

  }

  public function getImgDir(): string
  {
    return self::IMG_DIR;
  }
}
