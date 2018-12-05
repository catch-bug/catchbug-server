<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 22.11.18
 * @Time   : 19:56
 */

namespace rollbug;

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
   * @var \rollbug\os
   */
  public $os;

  public function __construct($obj)
  {
    $this->id = $obj->id;
    $this->timestamp = new \DateTime( $obj->timestamp, new \DateTimeZone('UTC'));
    $this->data = \json_decode($obj->data, false);

    $userAgent = new UserAgent($this->getUserAgent());
    $this->browser = new browser($userAgent);
    $this->os = new os($userAgent);
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
    if (\property_exists($this->data, 'request') &&
        \property_exists($this->data->request, 'method')){
      return $this->data->request->method;
    }

    return '';
  }

  /**
   * @return string
   */
  public function getURL(): string
  {
    if (\property_exists($this->data, 'request')
        && \property_exists($this->data->request, 'url')){
      return $this->data->request->url;
    }

    return '';
  }

  /**
   * @return string
   */
  public function getQueryString(): string
  {
    if (\property_exists($this->data, 'request') &&
        \property_exists($this->data->request, 'url')){
      return \parse_url($this->data->request->url, \PHP_URL_QUERY) ?? '';
    }

    return '';
  }

  /**
   * @return string
   */
  public function getUserIP(): string
  {
    if (\property_exists($this->data, 'request') &&
        \property_exists($this->data->request, 'user_ip')){
      return $this->data->request->user_ip;
    }

    return 'Unknown';
  }

  /**
   * @param bool $htmlSafe
   *
   * @return string
   */
  public function getExceptionMessage(bool $htmlSafe=false): string
  {
    $message = '';
    if (\property_exists($this->data->body, 'message')) {
      $message =  $this->data->body->message->body;
    }

    if (\property_exists($this->data->body, 'trace') &&
        \property_exists($this->data->body->trace->exception, 'message')) {
      $message =  $this->data->body->trace->exception->message;
    }

    if ($htmlSafe){
      $message = \htmlentities($message, ENT_QUOTES);
    }

    return $message;
  }

  /**
   * @return array
   */
  public function getGetArray(): array
  {
    if (\property_exists($this->data, 'request') &&
        \property_exists($this->data->request, 'GET')){
      return (array) $this->data->request->GET;

    }
    return [];
  }

  /**
   * @return array
   */
  public function getPostArray(): array
  {
    if (\property_exists($this->data, 'request') &&
        \property_exists($this->data->request, 'POST')){
      return (array) $this->data->request->POST;

    }
    return [];
  }

  /**
   * @return string
   */
  public function getCodeVersion(): string
  {
    if (\property_exists($this->data, 'code_version')) {
      return $this->data->code_version;
    }

    if (\property_exists($this->data, 'client') &&
        \property_exists($this->data->client, 'code_version')) {
      return $this->data->client->code_version;
    }

    if (\property_exists($this->data, 'server') &&
        \property_exists($this->data->server, 'code_version')) {
      return $this->data->server->code_version;
    }

    return '';
  }

  /**
   * @param bool $htmlSafe
   *
   * @return string
   */
  public function getRawJSON(bool $htmlSafe=false):string
  {
    if ($htmlSafe) {
      return \htmlentities(json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES);
    }

    return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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

    if (\property_exists($this->data, 'client') &&
        \property_exists($this->data->client, 'javascript') &&
        \property_exists($this->data->client->javascript, 'browser')){
      return $this->data->client->javascript->browser;
    }

    return '';
  }
}
