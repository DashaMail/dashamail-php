<?php

namespace DashaMail\Exception;

/** HTTP 429 — too many requests. See getRetryAfter() for how long to back off. */
class RateLimitException extends ApiException
{
    /** Seconds to wait before retrying, from the `Retry-After` header / error details, if known. */
    public function getRetryAfter()
    {
        if (isset($this->details['retry_after'])) {
            return (int)$this->details['retry_after'];
        }
        return null;
    }
}
