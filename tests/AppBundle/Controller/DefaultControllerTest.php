<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultControllerTest extends WebTestCase
{
    public function testUnauthenticatedIndexRedirectsToLoginPage()
    {
        // Given
        $client = static::createClient();
        $method = 'GET';
        $url = '/';

        // When
        $client->request($method, $url);
        $response = $client->getResponse();

        // Then
        $this->assertInstanceOf(RedirectResponse::class, $response);
        /** @var RedirectResponse $response */
        $this->assertEquals('http://localhost/login', $response->getTargetUrl());
    }

    public function testAuthenticatedIndexReturns200()
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'User1',
            'PHP_AUTH_PW' => 'pass123',
        ]);
        $method = 'GET';
        $url = '/';

        // When
        $client->request($method, $url);

        // Then
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
