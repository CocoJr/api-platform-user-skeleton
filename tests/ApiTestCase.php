<?php

namespace App\Tests;

use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiTestCase.
 */
class ApiTestCase extends WebTestCase
{
    use RefreshDatabaseTrait;

    const PAGINATION_MAX_IPP = 30;
    const TOTAL_USER = 512;
    const USER_EMAIL = 'user@localhost.com';
    const ADMIN_EMAIL = 'admin@localhost.com';
    const USER_PASSWORD = 'password';

    /** @var Client */
    protected $client;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    /**
     * Make a request. Use $userEmail to request with a logged user.
     *
     * @param string            $method
     * @param string            $uri
     * @param string|array|null $content
     * @param array             $headers
     * @param string|null       $userEmail
     *
     * @return Response
     */
    protected function request(string $method, string $uri, $content = null, array $headers = [], ?string $userEmail = null): Response
    {
        $server = ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'];
        if ($userEmail) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer '.$this->getUserApiKey($userEmail);
        }

        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'content-type') {
                $server['CONTENT_TYPE'] = $value;

                continue;
            }

            $server['HTTP_'.strtoupper(str_replace('-', '_', $key))] = $value;
        }

        if (is_array($content) && false !== preg_match('#^application/(?:.+\+)?json$#', $server['CONTENT_TYPE'])) {
            $content = json_encode($content);
        }

        $this->client->request($method, $uri, [], [], $server, $content);

        return $this->client->getResponse();
    }

    /**
     * Find IRI for ressource.
     *
     * @param string $resourceClass
     * @param array  $criteria
     *
     * @return string
     */
    protected function findOneIriBy(string $resourceClass, array $criteria): string
    {
        $resource = static::$container->get('doctrine')->getRepository($resourceClass)->findOneBy($criteria);

        return static::$container->get('api_platform.iri_converter')->getIriFromitem($resource);
    }

    /**
     * Get the user API Key based on his email.
     * @param string $email
     *
     * @return string
     */
    protected function getUserApiKey(string $email)
    {
        $user = static::$container
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneByEmail($email);

        return static::$container->get('lexik_jwt_authentication.jwt_manager')->create($user);
    }
}
