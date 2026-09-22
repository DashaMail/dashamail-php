<?php

namespace DashaMail;

use DashaMail\Exception\ApiException;
use DashaMail\Exception\NetworkException;

/**
 * Thin HTTP layer over the DashaMail REST API v2 (https://dashamail.ru/api/).
 *
 * Talks JSON over cURL with no third-party dependencies. Resource classes
 * (DashaMail\Resource\*) build on top of request()/requestMultipart(); most
 * applications should go through DashaMail\DashaMail rather than use this
 * class directly.
 */
class Client
{
    const DEFAULT_BASE_URL = 'https://api.dashamail.com/v2';
    const VERSION = '1.0.0';

    /** @var string */
    private $apiKey;

    /** @var string */
    private $baseUrl;

    /** @var int */
    private $timeout;

    /** @var string */
    private $userAgent;

    /**
     * @param string $apiKey  Account API key — Личный кабинет → Аккаунт → API и интеграции.
     * @param array  $options {
     *     @var string $base_url   Override the API origin, e.g. for a proxy or a mock server.
     *     @var int    $timeout    Request timeout in seconds. Default 30.
     *     @var string $user_agent Override the User-Agent header.
     * }
     */
    public function __construct($apiKey, array $options = [])
    {
        if (!is_string($apiKey) || $apiKey === '') {
            throw new \InvalidArgumentException('DashaMail API key must be a non-empty string.');
        }
        $this->apiKey = $apiKey;
        $this->baseUrl = isset($options['base_url']) ? rtrim($options['base_url'], '/') : self::DEFAULT_BASE_URL;
        $this->timeout = isset($options['timeout']) ? (int)$options['timeout'] : 30;
        $this->userAgent = isset($options['user_agent']) ? $options['user_agent'] : 'dashamail-php/' . self::VERSION;
    }

    /**
     * JSON request. Body is sent as `application/json`; on 2xx the decoded
     * `response.data` is returned wrapped in a Response, on error an
     * ApiException subclass is thrown.
     *
     * @param string     $method HTTP method
     * @param string     $path   Path relative to the API root, e.g. "/lists/123"
     * @param array      $query  Query-string parameters
     * @param array|null $body   Request body, JSON-encoded; omitted entirely when null
     * @return Response|BinaryResponse|null Response normally; null for a 204 No Content
     */
    public function request($method, $path, array $query = [], array $body = null)
    {
        $url = $this->buildUrl($path, $query);
        $headers = $this->baseHeaders();
        $payload = null;
        if ($body !== null) {
            $payload = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $headers[] = 'Content-Type: application/json';
        }

        $raw = $this->execute(strtoupper($method), $url, $headers, $payload);

        return $this->parseJsonResponse($raw['status'], $raw['body'], $raw['headers']);
    }

    /**
     * multipart/form-data request with a single file field — used by
     * POST /images/optimize. Other fields are sent alongside as plain
     * form fields (numbers/booleans are cast to strings by cURL).
     *
     * @param string $method
     * @param string $path
     * @param array  $query
     * @param array  $fields        Extra form fields, field name => scalar value
     * @param string $fileField     Name of the file form field (e.g. "file")
     * @param string $filePath      Local path to an existing, readable file
     * @param string|null $fileName Filename sent to the server; defaults to basename($filePath)
     * @param string|null $mimeType MIME type sent to the server; auto-detected when omitted
     */
    public function requestMultipart($method, $path, array $query, array $fields, $fileField, $filePath, $fileName = null, $mimeType = null)
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \InvalidArgumentException(sprintf('File not readable: %s', $filePath));
        }
        if ($mimeType === null) {
            $mimeType = @mime_content_type($filePath);
            if (!$mimeType) {
                $mimeType = 'application/octet-stream';
            }
        }

        $url = $this->buildUrl($path, $query);
        $headers = $this->baseHeaders();
        $fields[$fileField] = new \CURLFile($filePath, $mimeType, $fileName !== null ? $fileName : basename($filePath));

        $raw = $this->execute(strtoupper($method), $url, $headers, $fields);

        return $this->parseJsonResponse($raw['status'], $raw['body'], $raw['headers']);
    }

    /**
     * Same as requestMultipart(), but returns the raw response body instead
     * of decoding it as JSON — for POST /images/optimize?response=binary.
     */
    public function requestMultipartBinary($method, $path, array $query, array $fields, $fileField, $filePath, $fileName = null, $mimeType = null)
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \InvalidArgumentException(sprintf('File not readable: %s', $filePath));
        }
        if ($mimeType === null) {
            $mimeType = @mime_content_type($filePath);
            if (!$mimeType) {
                $mimeType = 'application/octet-stream';
            }
        }

        $url = $this->buildUrl($path, $query);
        $headers = $this->baseHeaders();
        $fields[$fileField] = new \CURLFile($filePath, $mimeType, $fileName !== null ? $fileName : basename($filePath));

        $raw = $this->execute(strtoupper($method), $url, $headers, $fields);

        return $this->parseBinaryResponse($raw['status'], $raw['body'], $raw['headers']);
    }

    /**
     * Same as request(), but returns the raw response body instead of
     * decoding it as JSON — for POST /images/optimize?response=binary with
     * a base64 JSON payload rather than a multipart upload.
     */
    public function requestBinary($method, $path, array $query = [], array $body = null)
    {
        $url = $this->buildUrl($path, $query);
        $headers = $this->baseHeaders();
        $payload = null;
        if ($body !== null) {
            $payload = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $headers[] = 'Content-Type: application/json';
        }

        $raw = $this->execute(strtoupper($method), $url, $headers, $payload);

        return $this->parseBinaryResponse($raw['status'], $raw['body'], $raw['headers']);
    }

    private function baseHeaders()
    {
        return [
            'Authorization: Bearer ' . $this->apiKey,
            'Accept: application/json',
        ];
    }

    private function buildUrl($path, array $query)
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');
        $query = array_filter($query, function ($v) {
            return $v !== null;
        });
        if ($query) {
            $url .= '?' . http_build_query($query);
        }
        return $url;
    }

    private function parseJsonResponse($status, $rawBody, array $headers)
    {
        if ($status === 204 || $rawBody === '') {
            return null;
        }

        $decoded = json_decode($rawBody, true);
        if (!is_array($decoded)) {
            throw new NetworkException(sprintf(
                'DashaMail API returned a non-JSON response (HTTP %d): %s',
                $status,
                substr($rawBody, 0, 500)
            ));
        }

        if ($status >= 200 && $status < 300) {
            $response = isset($decoded['response']) ? $decoded['response'] : $decoded;
            $data = is_array($response) && array_key_exists('data', $response) ? $response['data'] : $response;
            $message = is_array($response) && isset($response['msg']['text']) ? $response['msg']['text'] : null;
            $meta = isset($decoded['meta']) && is_array($decoded['meta']) ? $decoded['meta'] : [];
            return new Response($data, $meta, $message);
        }

        $error = isset($decoded['error']) && is_array($decoded['error'])
            ? $decoded['error']
            : ['code' => $status, 'message' => 'Unknown DashaMail API error'];
        throw ApiException::fromError($status, $error);
    }

    private function parseBinaryResponse($status, $rawBody, array $headers)
    {
        if ($status >= 200 && $status < 300) {
            $contentType = isset($headers['content-type']) ? $headers['content-type'] : 'application/octet-stream';
            return new BinaryResponse($rawBody, $contentType, $status);
        }

        $decoded = json_decode($rawBody, true);
        $error = is_array($decoded) && isset($decoded['error']) && is_array($decoded['error'])
            ? $decoded['error']
            : ['code' => $status, 'message' => 'Unknown DashaMail API error'];
        throw ApiException::fromError($status, $error);
    }

    /**
     * Low-level cURL call. Protected so tests can stub it out without a
     * real network connection — subclass Client and override execute().
     *
     * @param string       $method
     * @param string       $url
     * @param array        $headers Raw "Name: value" header lines
     * @param string|array|null $body String for a raw payload (e.g. JSON), array for multipart/form-data
     * @return array{status:int, body:string, headers:array<string,string>}
     */
    protected function execute($method, $url, array $headers, $body)
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new NetworkException('Unable to initialize a cURL handle.');
        }

        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => min($this->timeout, 10),
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false,
        ];
        if ($body !== null) {
            $opts[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);

        if ($raw === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            throw new NetworkException(sprintf('cURL error (%d): %s', $errno, $error), $errno);
        }

        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $rawHeaders = substr($raw, 0, $headerSize);
        $rawBody = (string)substr($raw, $headerSize);

        return [
            'status' => $status,
            'body' => $rawBody,
            'headers' => $this->parseHeaders($rawHeaders),
        ];
    }

    /** Parses the (possibly multi-block, on redirects) raw header text of the final response into a lowercase-keyed map. */
    private function parseHeaders($rawHeaders)
    {
        $blocks = preg_split('/\r\n\r\n|\n\n/', trim($rawHeaders));
        $lastBlock = end($blocks);
        $headers = [];
        foreach (preg_split('/\r\n|\n/', $lastBlock) as $line) {
            if (strpos($line, ':') === false) {
                continue;
            }
            list($name, $value) = explode(':', $line, 2);
            $headers[strtolower(trim($name))] = trim($value);
        }
        return $headers;
    }
}
