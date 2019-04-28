<?php

namespace App\Tests\User;

use App\Tests\ApiTestCase;

class ApiUserOperationGetTest extends ApiTestCase
{
    /**
     * Retrieves the user list - Error statement.
     *
     * @dataProvider providerTestUserOperationGetAuthError
     *
     * @param string|null $email
     * @param int         $code
     * @param string      $message
     */
    public function testUserOperationGetAuthError(?string $email, int $code, string $message): void
    {
        $response = $this->request('GET', '/users', null, ['Accept' => 'application/ld+json'], $email);

        $this->assertEquals($code, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('code', $responseData);
        $this->assertEquals($code, $responseData['code']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals($message, $responseData['message']);
    }

    /**
     * @see testUserOperationGetAuthError
     *
     * @return array
     */
    public function providerTestUserOperationGetAuthError(): array
    {
        return [
            [null, 401, 'JWT Token not found'],
            ['', 401, 'JWT Token not found'],
        ];
    }

    /**
     * Retrieves the user list - Error user not allowed statement.
     *
     * @dataProvider providerTestUserOperationGetNotAllow
     *
     * @param string|null $email
     */
    public function testUserOperationGetNotAllow(?string $email): void
    {
        $response = $this->request('GET', '/users', null, ['Accept' => 'application/ld+json'], $email);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('hydra:title', $responseData);
        $this->assertEquals('An error occurred', $responseData['hydra:title']);
        $this->assertArrayHasKey('hydra:description', $responseData);
        $this->assertEquals('Access Denied.', $responseData['hydra:description']);
    }

    /**
     * @see testUserOperationGetNotAllow
     *
     * @return array
     */
    public function providerTestUserOperationGetNotAllow(): array
    {
        return [
            [self::USER_EMAIL],
        ];
    }

    /**
     * Retrieves the user list - Success statement.
     *
     * @dataProvider providerTestUserOperationGetSuccess
     *
     * @param string $userEmail
     */
    public function testUserOperationGetSuccess(string $userEmail): void
    {
        $response = $this->request('GET', '/users', null, ['Accept' => 'application/ld+json'], $userEmail);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('hydra:totalItems', $responseData);
        $this->assertEquals(self::TOTAL_USER, $responseData['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $responseData);
        $this->assertCount(self::PAGINATION_MAX_IPP, $responseData['hydra:member']);
    }

    /**
     * @see testUserOperationGetSuccess
     *
     * @return array
     */
    public function providerTestUserOperationGetSuccess(): array
    {
        return [
            [self::ADMIN_EMAIL],
        ];
    }
}
