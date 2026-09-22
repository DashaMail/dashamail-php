<?php

namespace DashaMail\Exception;

/** HTTP 403 — the API key is valid but lacks the rights or scope for this action. */
class AuthorizationException extends ApiException
{
}
