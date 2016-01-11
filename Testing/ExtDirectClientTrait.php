<?php

namespace Modera\FoundationBundle\Testing;

use Modera\SecurityBundle\Entity\User;

/**
 * @author    Alex Plaksin <alex.plaksin@modera.net>
 * @copyright 2015 Modera Foundation
 */
trait ExtDirectClientTrait
{
    /**
     * Calls a URI.
     *
     * @param string $method        The request method
     * @param string $uri           The URI to fetch
     * @param array  $parameters    The Request parameters
     * @param array  $files         The files
     * @param array  $server        The server parameters (HTTP headers are referenced with a HTTP_ prefix as PHP does)
     * @param array  $content       The raw body data
     * @param bool   $changeHistory Whether to update the history or not (only used internally for back(), forward(), and reload())
     *
     * @return ExtDirectResponse[]
     */
    public function extDirectRequest($method, $uri, array $parameters = array(), array $files = array(), array $server = array(), array $content, $changeHistory = true)
    {
        static::$client->request($method, $uri, $parameters, $files, $server, json_encode($content), $changeHistory);

        $response = static::$client->getResponse();

        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        $content = json_decode($response->getContent(), true);

        $returnArray = [];
        foreach ($content as $responseItem) {
            $item = new ExtDirectResponse($responseItem, $response->getStatusCode());
            $item->parse();

            $returnArray[] = $item;
        }

        return $returnArray;
    }

    /**
     * Helper method for retrieving auth header used by firewall to authentifcate user against Direct routing.
     *
     * @param User $user
     *
     * @return array
     */
    protected static function getDirectAuthHeaders(User $user, $password = '1234')
    {
        return array(
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'PHP_AUTH_USER' => $user->getUsername(),
            'PHP_AUTH_PW' => $password,
        );
    }
}
