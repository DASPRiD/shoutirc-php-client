<?php
namespace ShoutIrc\Exception;

use ShoutIrc\Response;
use UnexpectedValueException;

class UnexpectedResponseException extends UnexpectedValueException implements ExceptionInterface
{
    /**
     * @param  Response $response
     * @return static
     */
    public static function fromResponse(Response $response)
    {
        return new static(sprintf(
            'Received unexpected response: 0x%s with data "%s"',
            str_pad(dechex($response->getCode()), 2, '0', STR_PAD_LEFT),
            $response->getData()
        ));
    }
}
