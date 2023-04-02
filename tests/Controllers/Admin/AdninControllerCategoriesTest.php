<?php

namespace App\Tests\Controllers\Admin;

use App\Entity\Category;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class AdninControllerCategoriesTest extends WebTestCase
{

    public function testTextOnPage(): void
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
        $client->disableReboot();
        $crawler = $client->request('GET', '/admin/su/categories');

        $this->assertSame('Categories list', $crawler->filter('h2')->text());
        $this->assertStringContainsString('Electronics', $client->getResponse()->getContent());
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
        $entityManager->close();
        $entityManager = null;
    }

    public function testNumberOfItems()
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
        $client->disableReboot();
        $crawler = $client->request('GET', '/admin/su/categories');
        $this->assertCount(21, $crawler->filter('option'));
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

    public function testNewCategory()
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
        $client->disableReboot();
        $entityManager->beginTransaction();
        $entityManager->getConnection()->setAutoCommit(false);
        $crawler = $client->request('GET', '/admin/su/categories');
        $form = $crawler->selectButton('Add')->form([
            'category[parent]' => 1,
            'category[name]' => 'Other Name'
        ]);
        $client->submit($form);
        $category = $entityManager->getRepository(Category::class)->findOneBy([
            'name'=> 'Other Name'
        ]);
        $this->assertNotNull($category);
        $this->assertSame('Other Name', $category->getName());
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

    public function testEditCategory()
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
        $client->disableReboot();
        $entityManager->beginTransaction();
        $entityManager->getConnection()->setAutoCommit(false);
        $crawler = $client->request('GET', '/admin/su/edit_category/1');
        $form = $crawler->selectButton('Save')->form([
            'category[parent]' => "null",
            'category[name]' => 'Electronics 2'
        ]);
        $client->submit($form);
        $category = $entityManager->getRepository(Category::class)->find(1);
        $this->assertNotNull($category);
        $this->assertSame('Electronics 2', $category->getName());
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

    public function testDeleteCategory()
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
        $client->disableReboot();
        $entityManager->beginTransaction();
        $entityManager->getConnection()->setAutoCommit(false);
        $client->request('GET', '/admin/su/delete_category/1');
        $category = $entityManager->getRepository(Category::class)->find(1);
        $this->assertNull($category);
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
