<?php

namespace App\Tests;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use function PHPSTORM_META\map;

class SecretControllerTest extends WebTestCase
{
    public function testSomething(): void
    {
        static::createClient();

        $this->storeAndGet();
        $this->acceptHeaderShouldBeRequired();
    }

    public function storeAndGet()
    {
        $SECRET_MESSAGE = 'Secret message';
        $REMAINING_VIEWS = 5;
        $EXPIRE_AFTER = (new DateTime('tomorrow'))->format('Y-m-d H:i:s');

        $client = $this->getClient();

        // Store a Secret
        $client->request(
            method: 'POST',
            uri: '/secret',
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'secret' => $SECRET_MESSAGE,
                'expireAfter' => $EXPIRE_AFTER,
                'expireAfterViews' => $REMAINING_VIEWS,
            ])
        );

        $storeResponse = $client->getResponse();
        $storeSecret = json_decode($storeResponse->getContent(), true);
        $storeSecretHash = $storeSecret['hash'];

        $this->assertEquals($storeSecret['secretText'], $storeSecret['secretText']);
        $this->assertEquals($storeSecret['remainingViews'], $REMAINING_VIEWS);

        // Get the previously stored Secret
        $client->request(
            method: 'GET',
            uri: "/secret/$storeSecretHash",
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        $getResponse = $client->getResponse();
        $getSecret = json_decode($getResponse->getContent(), true);
        $getSecretText = $getSecret['secretText'];
        $getSecretHash = $getSecret['hash'];

        $this->assertEquals($storeSecretHash, $getSecretHash);
        $this->assertEquals($getSecretText, $SECRET_MESSAGE);
        $this->assertEquals($getSecret['remainingViews'], $REMAINING_VIEWS - 1);

        $this->assertResponseIsSuccessful();
    }

    public function acceptHeaderShouldBeRequired()
    {
        $client = $this->getClient();

        $client->request(
            method: 'POST',
            uri: '/secret',
            server: [],
            content: json_encode([
                'secret' => '',
                'expireAfter' => '2025-01-01 00:00:00',
                'expireAfterViews' => 1,
            ])
        );

        $this->assertResponseStatusCodeSame(406);
    }
}
