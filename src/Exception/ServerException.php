<?php

namespace DashaMail\Exception;

/** HTTP 5xx — something failed on DashaMail's side. Usually safe to retry. */
class ServerException extends ApiException
{
}
