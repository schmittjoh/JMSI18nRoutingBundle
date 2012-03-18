<?php

namespace JMS\I18nRoutingBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class NotAcceptableException extends HttpException implements \Exception
{
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct(406, $message, $previous, array(), $code);
    }
}