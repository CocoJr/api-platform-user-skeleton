<?php

namespace App\Tests;

/**
 * Class ApiBaseTest
 */
class ApiBaseTest extends ApiTestCase
{
    /**
     * Test login error.
     *
     * @dataProvider providerTestLoginError
     *
     * @param string|null $username
     * @param string|null $password
     * @param int         $code
     * @param string      $message
     */
    public function testLoginError(?string $username, ?string $password, int $code, string $message): void
    {
        $response = $this->request(
            'GET',
            '/users/login',
            [
                'username' => $username,
                'password' => $password,
            ],
            ['Accept' => 'application/ld+json']
        );

        $this->assertEquals($code, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('code', $responseData);
        $this->assertEquals($code, $responseData['code']);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals($message, $responseData['message']);
    }

    /**
     * @see testLoginError
     *
     * @return array
     */
    public function providerTestLoginError(): array
    {
        return [
            ['', '', 401, 'Bad credentials'],
            ['user', '', 401, 'Bad credentials'],
            ['', self::USER_PASSWORD, 401, 'Bad credentials'],
            ['user', self::USER_PASSWORD, 401, 'Bad credentials'],
        ];
    }

    /**
     * Test login success.
     *
     * @dataProvider providerTestLoginSuccess
     *
     * @param string|null $username
     * @param string|null $password
     */
    public function testLoginSuccess(?string $username, ?string $password): void
    {
        $response = $this->request(
            'GET',
            '/users/login',
            [
                'username' => $username,
                'password' => $password,
            ],
            ['Accept' => 'application/ld+json']
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertNotEmpty($responseData['token']);

        $this->assertArrayHasKey('refresh_token', $responseData);
        $this->assertNotEmpty($responseData['refresh_token']);
    }

    /**
     * @see testLoginError
     *
     * @return array
     */
    public function providerTestLoginSuccess(): array
    {
        return [
            [self::USER_EMAIL, self::USER_PASSWORD],
            [self::ADMIN_EMAIL, self::USER_PASSWORD],
        ];
    }
}
