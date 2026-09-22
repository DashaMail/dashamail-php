<?php

namespace DashaMail\Exception;

/** The request never got an HTTP response (DNS, TLS, timeout, connection reset...). */
class NetworkException extends \RuntimeException
{
}
