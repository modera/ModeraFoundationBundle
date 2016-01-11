<?php

namespace Modera\FoundationBundle\Testing;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Alex Plaksin <alex.plaksin@modera.net>
 * @copyright 2015 Modera Foundation
 *
 * Helper class to wrap ExtDirect json response
 */
class ExtDirectResponse
{
    const STATUS_SUCCESS = 'success';
    const STATUS_EXCEPTION = 'exception';

    /**
     * @var array
     */
    private $response;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string
     */
    private $status;

    /**
     * @var bool
     */
    private $isSuccessful = false;

    /**
     * @var array
     */
    private $items;

    private $exception;

    public function __construct($response, $statusCode = 200)
    {
        $this->items = [];
        if (array_key_exists('result', $response)) {
            $this->response = $response['result'];
            $this->status = self::STATUS_SUCCESS;
        }
        if (array_key_exists('type', $response) && $response['type'] == 'exception') {
            $this->status = self::STATUS_EXCEPTION;
            $this->exception = $response['message'];
        }
        $this->statusCode = $statusCode;
    }

    public function parse()
    {
        if ($this->status == self::STATUS_SUCCESS) {
            if (array_key_exists('items', $this->response)) {
                $this->items = $this->response['items'];
            }
        } elseif ($this->status == self::STATUS_EXCEPTION) {
            $this->isSuccessful = false;
        }
    }

    public function getFullResponse()
    {
        return $this->response;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        if ($this->status == self::STATUS_SUCCESS) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }
}
