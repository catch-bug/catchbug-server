<?php
/**
 * @Project: RollbarServer
 * @User   : mira
 * @Date   : 22.11.18
 * @Time   : 19:56
 */

namespace rollbug;

use Sinergi\BrowserDetector\Os;
use Sinergi\BrowserDetector\UserAgent;

class occurrence
{
  /**
   * @var int occurence id
   */
  public $id;

  /**
   * @var \DateTime
   */
  public $timestamp;

  /**
   * @var \stdClass
   */
  public $data;

  /**
   * @var browser
   */
  public $browser;

  /**
   * @var \Sinergi\BrowserDetector\Os
   */
  public $os;

  public function __construct($obj)
  {
    $this->id = $obj->id;
    $this->timestamp = new \DateTime( $obj->timestamp, new \DateTimeZone('UTC'));
    $this->data = \json_decode($obj->data, false);

    $userAgent = new UserAgent($this->getUserAgent());
    $this->browser = new browser($userAgent);
    $this->os = new Os($userAgent);
  }

  /**
   * @param string             $format
   * @param \DateTimeZone|null $timezone
   *
   * @return string
   */
  public function getTimestampStr(string $format, \DateTimeZone $timezone=null): string
  {
    if ($timezone === null){
      $this->timestamp->setTimezone(new \DateTimeZone('UTC'));
    } else {
      $this->timestamp->setTimezone($timezone);
    }
    return $this->timestamp->format($format);
  }

  /**
   * @return string
   */
  public function getRequestMethod(): string
  {
    if (\property_exists($this->data, 'request') && \property_exists($this->data->request, 'method')){
      return $this->data->request->method;
    }

    return '';
  }

  /**
   * @return string
   */
  public function getURL(): string
  {
    if (\property_exists($this->data, 'request') && \property_exists($this->data->request, 'url')){
      return $this->data->request->url;
    }

    return '';
  }

  /**
   * @return string
   */
  public function getQueryString(): string
  {
    if (\property_exists($this->data, 'request') && \property_exists($this->data->request, 'url')){
      return \parse_url($this->data->request->url, \PHP_URL_QUERY);
    }

    return '';
  }

  /**
   * @return string
   */
  public function getUserIP(): string
  {
    if (\property_exists($this->data, 'request') && \property_exists($this->data->request, 'user_ip')){
      return $this->data->request->user_ip;
    }

    return '';
  }

  /**
   * @return string
   */
  public function getExceptionMessage(): string
  {
    if (\property_exists($this->data->body, 'message')) {
      return $this->data->body->message;
    }

    if (\property_exists($this->data->body, 'trace') && \property_exists($this->data->body->trace->exception, 'message')) {
      return $this->data->body->trace->exception->message;
    }

    return '';
  }

  /**
   * @return string
   */
  private function getUserAgent(): string
  {
    if (\property_exists($this->data, 'request') &&
        \property_exists($this->data->request, 'headers') &&
        \property_exists($this->data->request->headers, 'User-Agent')){
      return $this->data->request->headers->{'User-Agent'};
    }

    return '';
  }
}
