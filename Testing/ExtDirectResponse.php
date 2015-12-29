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
    private $isSuccessful;

    /**
     * @var array
     */
    private $items;

    private $exception;


    public function __construct($response, $statusCode=200)
    {
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
            $this->isSuccessful = $this->response['success'];
            if ($this->isSuccessful) {
                $this->items = $this->response['items'];
            } else {

            }
        } elseif ($this->status == self::STATUS_EXCEPTION) {
            $this->isSuccessful = false;
        }
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
        return $this->isSuccessful;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

}