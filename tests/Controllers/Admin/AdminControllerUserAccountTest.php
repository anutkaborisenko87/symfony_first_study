<?php

namespace App\Tests\Controllers\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerUserAccountTest extends WebTestCase
{
    public function testUserDeleteAccount()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->beginTransaction();
        $entityManager->getConnection()->setAutoCommit(false);
        $client->disableReboot();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'anna_2user@default.ua']);
        $client->loginUser($user);
        $client->followRedirects();
        $client->request('GET', '/admin/delete_account');
        $user = $entityManager->getRepository(User::class)->find(3);
        $this->assertNull($user);
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
    public function testUserChangedPassword()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->beginTransaction();
        $entityManager->getConnection()->setAutoCommit(false);
        $client->disableReboot();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'anna_2user@default.ua']);
        $client->loginUser($user);
        $client->followRedirects();
        $crawler = $client->request('GET', '/admin/');
        $form = $crawler->selectButton('Save')->form([
            'user[name]' => 'name',
            'user[last_name]' => 'Last name',
            'user[email]' => 'email@email.ua',
            'user[password][first]' => 'password',
            'user[password][second]' => 'password'
        ]);
        $client->submit($form);
        $user = $entityManager->getRepository(User::class)->find(3);
        $this->assertSame('name', $user->getName());
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
