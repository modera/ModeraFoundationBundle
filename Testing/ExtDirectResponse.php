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
        $this->response = $response['result'];
        $this->statusCode = $statusCode;
    }

    public function parse()
    {
        $this->isSuccessful = $this->response['success'];
        if ($this->isSuccessful) {
            $this->items = $this->response['items'];
        } else {

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