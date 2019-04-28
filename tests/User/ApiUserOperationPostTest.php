<?php

namespace App\Tests\User;

use App\Tests\ApiTestCase;

class ApiUserOperationPostTest extends ApiTestCase
{
    /**
     * Test create an user - Error statement.
     *
     * @dataProvider providerTestUserOperationPostError
     *
     * @param string|null $email
     * @param string|null $password
     * @param int         $code
     * @param array|null  $violations
     */
    public function testUserOperationPostError(?string $email, ?string $password, int $code, ?array $violations = null): void
    {
        $data = [];
        if ($email) {
            $data['email'] = $email;
        }
        if ($password) {
            $data['password'] = $password;
        }

        $response = $this->request(
            'POST',
            '/users',
            $data
        );

        $this->assertEquals($code, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('hydra:title', $responseData);
        $this->assertEquals('An error occurred', $responseData['hydra:title']);$this->assertArrayHasKey('violations', $responseData);
        $this->assertEquals($violations, $responseData['violations']);
    }

    /**
     * @see testUserOperationPostError
     *
     * @return array
     */
    public function providerTestUserOperationPostError(): array
    {
        return [
            [self::ADMIN_EMAIL, self::USER_PASSWORD, 400, [['propertyPath' => 'email', 'message' => 'This value is already used.']]],
            [self::USER_EMAIL, self::USER_PASSWORD, 400, [['propertyPath' => 'email', 'message' => 'This value is already used.']]],
            ['invalidEmail', self::USER_PASSWORD, 400, [['propertyPath' => 'email', 'message' => 'This value is not a valid email address.']]],
            [null, null, 400, [['propertyPath' => 'email', 'message' => 'This value should not be blank.'], ['propertyPath' => 'password', 'message' => 'This value should not be blank.']]],
        ];
    }

    /**
     * Test create an user - Success statement.
     *
     * @dataProvider providerTestUserOperationPostSuccess
     *
     * @param string $email
     * @param string $password
     */
    public function testUserOperationPostSuccess(string $email, string $password): void
    {
        $response = $this->request(
            'POST',
            '/users',
            [
                'email' => $email,
                'password' => $password,
            ]
        );

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(self::TOTAL_USER + 1, $responseData['id']);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertEquals($email, $responseData['email']);
        $this->assertArrayHasKey('roles', $responseData);
        $this->assertEquals(['ROLE_USER'], $responseData['roles']);
        $this->assertArrayNotHasKey('password', $responseData);
        $this->assertArrayHasKey('projects', $responseData);
        $this->assertEquals([], $responseData['projects']);
    }

    /**
     * @see testUserOperationPostSuccess
     *
     * @return array
     */
    public function providerTestUserOperationPostSuccess(): array
    {
        return [
            ['TestUserOperationPostSuccess@localhost.com', self::USER_PASSWORD],
        ];
    }
}
