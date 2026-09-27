<?php

namespace DashaMail\Exception;

/**
 * Thrown for any error response from the DashaMail API (HTTP status >= 400
 * with a JSON {"error": {"code", "message", "details"}} body).
 *
 * The HTTP status and the DashaMail error `code` are different numbers:
 * `code` is stable across API versions and documented at
 * https://dashamail.ru/api/errors/, while the HTTP status is a coarser
 * REST-ification of it. Use getApiCode() to branch on specific errors.
 */
class ApiException extends \RuntimeException
{
    /** @var int */
    protected $httpStatus;

    /** @var int */
    protected $apiCode;

    /** @var array */
    protected $details;

    public function __construct($message, $httpStatus, $apiCode = 0, array $details = [])
    {
        parent::__construct($message, $httpStatus);
        $this->httpStatus = $httpStatus;
        $this->apiCode = $apiCode;
        $this->details = $details;
    }

    /**
     * Build the most specific exception subclass for a given HTTP status.
     *
     * @param int   $httpStatus
     * @param array $error      Decoded `error` object: ['code'=>, 'message'=>, 'details'=>]
     * @return ApiException
     */
    public static function fromError($httpStatus, array $error)
    {
        $message = isset($error['message']) ? (string)$error['message'] : 'DashaMail API error';
        $code = isset($error['code']) ? (int)$error['code'] : $httpStatus;
        $details = isset($error['details']) && is_array($error['details']) ? $error['details'] : [];

        switch ((int)$httpStatus) {
            case 401:
                return new AuthenticationException($message, $httpStatus, $code, $details);
            case 402:
                return new PaymentRequiredException($message, $httpStatus, $code, $details);
            case 403:
                return new AuthorizationException($message, $httpStatus, $code, $details);
            case 404:
                return new NotFoundException($message, $httpStatus, $code, $details);
            case 409:
                return new ConflictException($message, $httpStatus, $code, $details);
            case 413:
                return new PayloadTooLargeException($message, $httpStatus, $code, $details);
            case 422:
                return new ValidationException($message, $httpStatus, $code, $details);
            case 429:
                return new RateLimitException($message, $httpStatus, $code, $details);
        }

        if ($httpStatus >= 500) {
            return new ServerException($message, $httpStatus, $code, $details);
        }

        return new self($message, $httpStatus, $code, $details);
    }

    /** HTTP status code of the response (e.g. 422). */
    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    /** DashaMail's own stable error code, see https://dashamail.ru/api/errors/ */
    public function getApiCode()
    {
        return $this->apiCode;
    }

    /** Extra structured context the API attached to the error, if any. */
    public function getDetails()
    {
        return $this->details;
    }
}
