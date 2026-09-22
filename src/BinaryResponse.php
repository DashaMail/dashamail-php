<?php

namespace DashaMail;

/**
 * Raw bytes returned by an endpoint that can answer outside JSON —
 * currently only POST /images/optimize?response=binary.
 */
class BinaryResponse
{
    /** @var string */
    private $body;

    /** @var string */
    private $contentType;

    /** @var int */
    private $httpStatus;

    public function __construct($body, $contentType, $httpStatus)
    {
        $this->body = $body;
        $this->contentType = $contentType;
        $this->httpStatus = $httpStatus;
    }

    /** Raw response body bytes. */
    public function getBody()
    {
        return $this->body;
    }

    /** Content-Type header sent back by the API, e.g. "image/jpeg". */
    public function getContentType()
    {
        return $this->contentType;
    }

    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    /** Write the bytes to a file. Returns bytes written, same as file_put_contents(). */
    public function saveTo($path)
    {
        return file_put_contents($path, $this->body);
    }
}
