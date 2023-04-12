<?php

namespace App\Tests\Controllers\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerUsersTest extends WebTestCase
{
    public function testUserDeleted(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->beginTransaction();
        $entityManager->getConnection()->setAutoCommit(false);
        $client->disableReboot();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.ua']);
        $client->loginUser($user);
        $client->followRedirects();
        $client->request('GET', '/admin/su/delete-user/3');
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
}
