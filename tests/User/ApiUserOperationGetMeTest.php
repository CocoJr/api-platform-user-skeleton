<?php

namespace App\Tests\User;

use App\Tests\ApiTestCase;

class ApiUserOperationGetMeTest extends ApiTestCase
{
    /**
     * Retrieves the logged user - Error statement.
     *
     * @dataProvider providerTestUserOperationGetMeError
     *
     * @param string|null $email
     * @param int         $code
     * @param string      $message
     */
    public function testUserOperationGetMeError(?string $email, int $code, string $message): void
    {
        $response = $this->request('GET', '/users/me', null, ['Accept' => 'application/ld+json'], $email);

        $this->assertEquals($code, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('code', $responseData);
        $this->assertEquals($code, $responseData['code']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals($message, $responseData['message']);
    }

    /**
     * @see testUserOperationGetMeError
     *
     * @return array
     */
    public function providerTestUserOperationGetMeError(): array
    {
        return [
            [null, 401, 'JWT Token not found'],
            ['', 401, 'JWT Token not found'],
        ];
    }

    /**
     * Retrieves the logged user - Success statement.
     *
     * @dataProvider providerTestUserOperationGetMeSuccess
     *
     * @param string $userEmail
     */
    public function testUserOperationGetMeSuccess(string $userEmail): void
    {
        $response = $this->request('GET', '/users/me', null, ['Accept' => 'application/ld+json'], $userEmail);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
    }

    /**
     * @see testUserOperationGetMeSuccess
     *
     * @return array
     */
    public function providerTestUserOperationGetMeSuccess(): array
    {
        return [
            [self::ADMIN_EMAIL],
        ];
    }
}
