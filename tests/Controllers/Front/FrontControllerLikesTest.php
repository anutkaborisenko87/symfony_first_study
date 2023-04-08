<?php

namespace App\Tests\Controllers\Front;

use App\Entity\User;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontControllerLikesTest extends WebTestCase
{

    public function testLike()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->beginTransaction();
        $entityManager->getConnection()->setAutoCommit(false);
        $client->disableReboot();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'anna_2user@default.ua']);
        $client->loginUser($user);
        $client->followRedirects();
        $client->request('POST', '/video-list/12/like');
        $crawler =$client->request('GET', '/video-list/category/movies_4');
        $this->assertSame('(1)', $crawler->filter('small.number-of-likes-12')->text());
        $authenticationUtils = $client->getContainer()->get('security.token_storage');
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
        if ($entityManager->getConnection()->isTransactionActive()) {
            $entityManager->rollback();
        }
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
        $entityManager->close();
        $entityManager = null;
    }

    public function testDisLike()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->beginTransaction();
        $entityManager->getConnection()->setAutoCommit(false);
        $client->disableReboot();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'anna_2user@default.ua']);
        $client->loginUser($user);
        $client->followRedirects();
        $client->request('POST', '/video-list/12/dislike');
        $crawler =$client->request('GET', '/video-list/category/movies_4');
        $this->assertSame('(1)', $crawler->filter('small.number-of-dislikes-12')->text());
        $authenticationUtils = $client->getContainer()->get('security.token_storage');
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
        if ($entityManager->getConnection()->isTransactionActive()) {
            $entityManager->rollback();
        }
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
        $entityManager->close();
        $entityManager = null;
    }

    public function testNumberOfLikedVideos()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->beginTransaction();
        $entityManager->getConnection()->setAutoCommit(false);
        $client->disableReboot();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'anna_2user@default.ua']);
        $client->loginUser($user);
        $client->followRedirects();
        $client->request('POST', '/video-list/12/like');
        $client->request('POST', '/video-list/12/like');
        $crawler = $client->request('GET', '/admin/videos');
        $this->assertEquals(4, $crawler->filter('tbody tr')->count());
        $authenticationUtils = $client->getContainer()->get('security.token_storage');
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
        if ($entityManager->getConnection()->isTransactionActive()) {
            $entityManager->rollback();
        }
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
        $entityManager->close();
        $entityManager = null;
    }

    public function testNumberOfUnLikedVideos()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->beginTransaction();
        $entityManager->getConnection()->setAutoCommit(false);
        $client->disableReboot();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'anna_2user@default.ua']);
        $client->loginUser($user);
        $client->followRedirects();
        $client->request('POST', '/video-list/12/like');
        $client->request('POST', '/video-list/12/unlike');
        $client->request('POST', '/video-list/8/unlike');
        $client->request('POST', '/video-list/10/unlike');
        $crawler = $client->request('GET', '/admin/videos');
        $this->assertEquals(1, $crawler->filter('tbody tr')->count());
        $authenticationUtils = $client->getContainer()->get('security.token_storage');
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
        if ($entityManager->getConnection()->isTransactionActive()) {
            $entityManager->rollback();
        }
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
        $entityManager->close();
        $entityManager = null;
    }
}
