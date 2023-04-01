<?php

namespace App\Tests\Controllers\Admin;

use App\Entity\User;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdminControllerSecurityTest extends WebTestCase
{
    public $client;
    /**
     * @var ContainerInterface
     */
    public $entityManager;

    /**
     * @dataProvider getUrlsRegularUsers
     * @param string $httpMethod
     * @param string $url
     * @return void
     */
    public function testAccessDeniedForRegularUser(string $httpMethod, string $url)
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'anna_2user@default.ua']);
        $client->loginUser($user);
        $client->request($httpMethod, $url);
        $this->assertSame(403, $client->getResponse()->getStatusCode());
        $authenticationUtils = $client->getContainer()->get('security.token_storage');
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
    }
    public function getUrlsRegularUsers(): Generator
    {
        yield ['GET', '/admin/su/categories'];
        yield ['GET', '/admin/su/edit_category/1'];
        yield ['GET', '/admin/su/delete_category/1'];
        yield ['GET', '/admin/su/users'];
        yield ['GET', '/admin/su/upload-video'];
    }
    public function testAccessForSu()
    {
        $client = static::createClient();
        $authenticationUtils = $client->getContainer()->get('security.token_storage');
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.ua']);
        $client->loginUser($user);
        $crawler = $client->request('GET', '/admin/su/categories');
        $this->assertSame('Categories list', $crawler->filter('h2')->text());
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
    }
}
