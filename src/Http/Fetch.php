<?php

namespace Luthier\Http;

use Exception;

class Fetch
{
  /**
   * Realiza requisições get com CURL
   */
  public static function get(string $url, ?array $headers = null, int $timeout = 180)
  {

    try {
      $curl = curl_init();

      curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CONNECTTIMEOUT => $timeout,
        CURLOPT_TIMEOUT => $timeout
      ]);

      $response = curl_exec($curl);
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    } catch (\Throwable $th) {
      throw new Exception("Erro ao realizar requisição. " . $th->getMessage(), 409);
    } finally {
      curl_close($curl);
    }

    return [
      'httpCode' => $httpCode,
      'response' => $response
    ];
  }

  /**
   * Realiza requisições POST com CURL
   */
  public static function post(string $url, string $body,  ?array $headers = null, string $contentType = 'application/json', int $timeout = 180)
  {
    try {

      $curl = curl_init();

      $finalHeaders = array_merge(["Content-Type: $contentType"], $headers);

      curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $finalHeaders,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_CONNECTTIMEOUT => $timeout,
        CURLOPT_TIMEOUT => $timeout
      ]);

      $response = curl_exec($curl);
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    } catch (\Throwable $th) {
      throw new Exception("Erro ao realizar requisição." . $th->getMessage(), 409);
    } finally {
      curl_close($curl);
    }

    return [
      'httpCode' => $httpCode,
      'response' => $response
    ];
  }

  /**
   * Realiza requisições PUT com CURL
   */
  public static function put(string $url, string $body, ?array $headers = null, string $contentType = 'application/json', $timeout = 180)
  {
    try {
      $curl = curl_init();

      $finalHeaders = array_merge(["Content-Type: $contentType"], $headers);

      curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $finalHeaders,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_CONNECTTIMEOUT => $timeout,
        CURLOPT_TIMEOUT => $timeout
      ]);

      $response = curl_exec($curl);
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    } catch (\Throwable $th) {
      throw new Exception("Erro ao realizar requisição." . $th->getMessage(), 409);
    } finally {
      curl_close($curl);
    }

    return [
      'httpCode' => $httpCode,
      'response' => $response
    ];
  }

  /**
   * Realiza requisições DELETE com CURL
   */
  public static function delete(string $url, ?array $headers, int $timeout = 180)
  {
    try {
      $curl = curl_init();

      curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CONNECTTIMEOUT => $timeout,
        CURLOPT_TIMEOUT => $timeout
      ]);

      $response = curl_exec($curl);
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    } catch (\Throwable $th) {
      throw new Exception("Erro ao realizar requisição." . $th->getMessage(), 409);
    } finally {
      curl_close($curl);
    }

    return [
      'httpCode' => $httpCode,
      'response' => $response
    ];
  }
}
