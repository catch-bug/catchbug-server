<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 27.11.18
 * @Time   : 15:49
 */

namespace catchbug;


class os extends \Sinergi\BrowserDetector\Os
{
  private $logoDirs = array(
      'unknown'       => 'fake',
      'OS X'          => 'apple',
      'iOS'           => 'apple',
      'SymbOS'        => 'symbian',
      'Windows'       => 'windows_xp',
      'Android'       => 'android',
      'Linux'         => 'linux',
      'Nokia'         => 'nokia',
      'BlackBerry'    => 'blackberry',
      'FreeBSD'       => 'freebsd',
      'OpenBSD'       => 'openbsd',
      'NetBSD'        => 'netbsd',
      'OpenSolaris'   => 'open_solaris',
      'SunOS'         => 'sunos',
      'OS2'           => 'os2',
      'BeOS'          => 'beos',
      'Windows Phone' => 'windows_mobile',
      'Chrome OS'     => 'google_chrome'
  );

  const IMG_DIR = '/img/os_logos/';

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
