<?php

namespace App\Tests\Controllers\Admin;

use App\Entity\Category;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdninControllerCategoriesTest extends WebTestCase
{
    public $client;
    /**
     * @var ContainerInterface
     */
    public $entityManager;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.ua']);
        $this->client->loginUser($user);
        $this->client->disableReboot();
        $this->entityManager->beginTransaction();
        $this->entityManager->getConnection()->setAutoCommit(false);
        static::$kernel = static::bootKernel();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }
        $authenticationUtils = $this->client->getContainer()->get('security.token_storage');
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testTextOnPage(): void
    {

        $crawler = $this->client->request('GET', '/admin/su/categories');

        $this->assertSame('Categories list', $crawler->filter('h2')->text());
        $this->assertStringContainsString('Electronics', $this->client->getResponse()->getContent());
    }

    public function testNumberOfItems()
    {
        $crawler = $this->client->request('GET', '/admin/su/categories');
        $this->assertCount(21, $crawler->filter('option'));

    }

    public function testNewCategory()
    {
        $crawler = $this->client->request('GET', '/admin/su/categories');
        $form = $crawler->selectButton('Add')->form([
            'category[parent]' => 1,
            'category[name]' => 'Other Name'
        ]);
        $this->client->submit($form);
        $category = $this->entityManager->getRepository(Category::class)->findOneBy([
            'name'=> 'Other Name'
        ]);
        $this->assertNotNull($category);
        $this->assertSame('Other Name', $category->getName());
    }

    public function testEditCategory()
    {
        $crawler = $this->client->request('GET', '/admin/su/edit_category/1');
        $form = $crawler->selectButton('Save')->form([
            'category[parent]' => "null",
            'category[name]' => 'Electronics 2'
        ]);
        $this->client->submit($form);
        $category = $this->entityManager->getRepository(Category::class)->find(1);
        $this->assertNotNull($category);
        $this->assertSame('Electronics 2', $category->getName());
    }

    public function testDeleteCategory()
    {
        $crawler = $this->client->request('GET', '/admin/su/delete_category/1');
        $category = $this->entityManager->getRepository(Category::class)->find(1);
        $this->assertNull($category);
    }
}
