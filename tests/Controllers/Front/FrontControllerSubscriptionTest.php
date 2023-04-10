<?php

namespace App\Tests\Controllers\Front;

use App\Entity\Subscription;
use App\Entity\User;
use DateTime;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontControllerSubscriptionTest extends WebTestCase
{
    /**
     * @dataProvider urlsWithVideo
     */
    public function testLoggedInUserDoesNotSeeTextForNoMembers($url): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->beginTransaction();
        $entityManager->getConnection()->setAutoCommit(false);
        $client->disableReboot();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'anna@default.ua']);
        $client->loginUser($user);
        $client->followRedirects();
        $client->request('GET', $url);
        $this->assertStringNotContainsString('Video for <b>MEMBERS</b> only.', $client->getResponse()->getContent());

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

    /**
     * @dataProvider urlsWithVideo
     **/
    public function testNotLoggedInUserSeesTextForNoMembers($url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);
        $authenticationUtils = $client->getContainer()->get('security.token_storage');
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
        $this->assertStringContainsString('Video for <b>MEMBERS</b> only.', $client->getResponse()->getContent());

    }

    /**
     * @dataProvider urlsWithVideo2
     * @param $url
     * @return void
     */
    public function testExpiredSubscription($url): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->beginTransaction();
        $entityManager->getConnection()->setAutoCommit(false);
        $client->disableReboot();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'anna@default.ua']);
        $client->loginUser($user);
        $client->followRedirects();
        $subscription = $entityManager->getRepository(Subscription::class)->find(2);
        $invalid_date = (new DateTime())->modify('-1 day');
        $subscription->setValidTo($invalid_date);
        $entityManager->persist($subscription);
        $entityManager->flush();
        $client->request('GET', $url);
        $this->assertStringContainsString('Video for <b>MEMBERS</b> only.', $client->getResponse()->getContent());

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

    public function urlsWithVideo(): Generator
    {
        yield ['/video-list/category/movies_4'];
        yield ['/search-results?query=movies'];
    }

    public function urlsWithVideo2(): Generator
    {
        yield ['/video-list/category/movies_4'];
        yield ['/video-list/category/toys_2'];
        yield ['/search-results?query=movies'];
    }


}
