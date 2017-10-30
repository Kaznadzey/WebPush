<?php

namespace Nazz\WebPush\Exception;

/**
 * Class InvalidClientIdException
 */
class InvalidClientIdException extends \Exception
{
    protected $message = 'Client id: %s is not defined.';

    /**
     * InvalidClientIdException constructor.
     *
     * @param string $clientId
     */
    public function __construct($clientId)
    {
        $message = sprintf(
            $this->message,
            $clientId
        );

        parent::__construct($message);
    }
}
