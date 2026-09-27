<?php

namespace DashaMail\Exception;

/** HTTP 409 — the request conflicts with the resource's current state (e.g. a blacklisted address). */
class ConflictException extends ApiException
{
}
