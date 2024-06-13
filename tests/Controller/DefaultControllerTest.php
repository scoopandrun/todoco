<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    use UsersTrait;

    public function testUnauthenticatedIndexReturnsUnauthorizedResponse(): void
    {
        // Given
        $client = $this->getUnauthenticatedClient(followRedirects: false);
        $method = 'GET';
        $url = '/';

        // When
        $client->request($method, $url);

        // Then
        $this->assertResponseStatusCodeSame(401);
    }

    public function testAuthenticatedIndexReturns200(): void
    {
        // Given
        $client = $this->getAuthenticatedClient('User1', followRedirects: false);
        $method = 'GET';
        $url = '/';

        // When
        $client->request($method, $url);

        // Then
        $this->assertResponseStatusCodeSame(200);
    }
}
