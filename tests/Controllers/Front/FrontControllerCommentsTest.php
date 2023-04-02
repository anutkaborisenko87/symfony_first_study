<?php

namespace App\Tests\Controllers\Front;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class FrontControllerCommentsTest extends WebTestCase
{


    public function testNotLoggedInUser(): void
    {
        $client = static::createClient();
        $authenticationUtils = $client->getContainer()->get('security.token_storage');
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
        $crawler = $client->request('GET', '/video-details/16');
        $form = $crawler->selectButton('Add')->form([
            'comment' => 'Test comment'
        ]);
        $client->submit($form);
        $this->assertStringContainsString('/login', $client->getResponse()->getTargetUrl());

    }

    public function testNewCommentAndNumberOfComments()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->beginTransaction();
        $entityManager->getConnection()->setAutoCommit(false);
        $client->disableReboot();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'anna_2user@default.ua']);
        $client->loginUser($user);
        $client->followRedirects();
        $crawler = $client->request('GET', '/video-details/16');
        $form = $crawler->selectButton('Add')->form([
            'comment' => 'Test comment'
        ]);
        $client->submit($form);
        $this->assertStringContainsString('Test comment', $client->getResponse()->getContent());
        $crawler = $client->request('GET', '/video-list/category/toys_2');
        $this->assertSame('Comments (1)', $crawler->filter('a.ml-2')->text());
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
