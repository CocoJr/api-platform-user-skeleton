<?php

namespace App\Tests\User;

use App\Tests\ApiTestCase;

class ApiUserOperationPutTest extends ApiTestCase
{
    /**
     * Test update an user - Error statement.
     *
     * @dataProvider providerTestUserOperationPutError
     *
     * @param int         $userIdToUpdate
     * @param string|null $emailLogin
     * @param string|null $email
     * @param int         $code
     * @param array|null  $violations
     */
    public function testUserOperationPutError(int $userIdToUpdate, ?string $emailLogin, ?string $email, int $code, ?array $violations = null): void
    {
        $data = [];
        if ($email) {
            $data['email'] = $email;
        }

        $response = $this->request(
            'PUT',
            '/users/'.$userIdToUpdate,
            $data,
            [],
            $emailLogin
        );

        $this->assertEquals($code, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('hydra:title', $responseData);
        $this->assertEquals('An error occurred', $responseData['hydra:title']);
        if ($violations) {
            $this->assertArrayHasKey('violations', $responseData);
            $this->assertEquals($violations, $responseData['violations']);
        }
    }

    /**
     * @see testUserOperationPutError
     *
     * @return array
     */
    public function providerTestUserOperationPutError(): array
    {
        return [
            [2, self::USER_EMAIL, self::ADMIN_EMAIL, 403],
            [1, self::ADMIN_EMAIL, self::ADMIN_EMAIL, 400, [['propertyPath' => 'email', 'message' => 'This value is already used.']]],
            [2, self::ADMIN_EMAIL, self::USER_EMAIL, 400, [['propertyPath' => 'email', 'message' => 'This value is already used.']]],
            [2, self::ADMIN_EMAIL, 'invalidEmail', 400, [['propertyPath' => 'email', 'message' => 'This value is not a valid email address.']]],
        ];
    }

    /**
     * Test update an user - Success statement.
     *
     * @dataProvider providerTestUserOperationPutSuccess
     *
     * @param int         $userIdToUpdate
     * @param string|null $emailLogin
     * @param string|null $email
     */
    public function testUserOperationPutSuccess(int $userIdToUpdate, ?string $emailLogin, ?string $email): void
    {
        $data = [];
        if ($email) {
            $data['email'] = $email;
        }

        $response = $this->request(
            'PUT',
            '/users/'.$userIdToUpdate,
            $data,
            [],
            $emailLogin
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
        if ($email) {
            $this->assertEquals($email, $responseData['email']);
        } else {
            $this->assertNotNull($responseData['email']);
        }
    }

    /**
     * @see testUserOperationPutSuccess
     *
     * @return array
     */
    public function providerTestUserOperationPutSuccess(): array
    {
        return [
            [1, self::USER_EMAIL, null],
            [1, self::USER_EMAIL, 'user_new_email@localhost.com'],
            [2, self::ADMIN_EMAIL, null],
            [2, self::ADMIN_EMAIL, 'admin_new_email@localhost.com'],
        ];
    }
}
