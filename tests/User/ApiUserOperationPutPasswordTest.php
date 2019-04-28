<?php

namespace App\Tests\User;

use App\Tests\ApiTestCase;

class ApiUserOperationPutPasswordTest extends ApiTestCase
{
    /**
     * Test update an user's password - Error statement.
     *
     * @dataProvider providerTestUserOperationPutPasswordError
     *
     * @param int         $userIdToUpdate
     * @param string|null $emailLogin
     * @param string|null $password
     * @param int         $code
     * @param array|null  $violations
     */
    public function testUserOperationPutPasswordError(int $userIdToUpdate, ?string $emailLogin, ?string $password, int $code, ?array $violations = null): void
    {
        $data = ['password' => $password];

        $response = $this->request(
            'PUT',
            '/users/'.$userIdToUpdate.'/password',
            $data,
            [],
            $emailLogin
        );

        $responseData = json_decode($response->getContent(), true);

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
     * @see testUserOperationPutPasswordError
     *
     * @return array
     */
    public function providerTestUserOperationPutPasswordError(): array
    {
        return [
            [2, self::USER_EMAIL, self::ADMIN_EMAIL, 403],
            [1, self::ADMIN_EMAIL, '', 400, [['propertyPath' => 'password', 'message' => 'This value should not be blank.']]],
            [1, self::ADMIN_EMAIL, 'a', 400, [['propertyPath' => 'password', 'message' => 'This value is too short. It should have 4 characters or more.']]],
            [2, self::ADMIN_EMAIL, 'aaa', 400, [['propertyPath' => 'password', 'message' => 'This value is too short. It should have 4 characters or more.']]],
            [1, self::USER_EMAIL, substr(str_shuffle(str_repeat('a', 41)),0, 41), 400, [['propertyPath' => 'password', 'message' => 'This value is too long. It should have 40 characters or less.']]],
        ];
    }

    /**
     * Test update an user's password - Success statement.
     *
     * @dataProvider providerTestUserOperationPutPasswordSuccess
     *
     * @param int         $userIdToUpdate
     * @param string|null $emailLogin
     * @param string|null $email
     */
    public function testUserOperationPutPasswordSuccess(int $userIdToUpdate, ?string $emailLogin, ?string $email): void
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
     * @see testUserOperationPutPasswordSuccess
     *
     * @return array
     */
    public function providerTestUserOperationPutPasswordSuccess(): array
    {
        return [
            [1, self::USER_EMAIL, null],
            [1, self::USER_EMAIL, 'user_new_email@localhost.com'],
            [2, self::ADMIN_EMAIL, null],
            [2, self::ADMIN_EMAIL, 'admin_new_email@localhost.com'],
        ];
    }
}
