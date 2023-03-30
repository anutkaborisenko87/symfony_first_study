<?php

namespace App\Utils\AbstractClasses;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class CategoryTreeAbstractClass
{
    protected static $dbconnection;
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var UrlGeneratorInterface
     */
    public $urlGenerator;
    /**
     * @var array
     */
    public $categotiesArrayFromDb;
    public $categotyList;

    /**
     * @throws Exception
     */
    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->categotiesArrayFromDb = $this->getCategories();
    }

    abstract public function getCategoryList(array $categories_array);

    /**
     * @throws Exception
     */
    private function getCategories(): array
    {
        if(self::$dbconnection) {
            return self::$dbconnection;
        } else {
            $conn = $this->entityManager->getConnection();
            $sql = "SELECT * FROM categories";
            $stmt =$conn->prepare($sql);
            return $stmt->executeQuery()->fetchAllAssociative();
        }
    }

    public function buildTree(int $parent_id = null): array
    {
        $subcategory = [];
        foreach ($this->categotiesArrayFromDb as $category) {
            if ($category['parent_id'] == $parent_id) {
                $children = $this->buildTree($category['id']);
                if ($children)
                {
                    $category['children'] = $children;
                }
                $subcategory[] = $category;
            }
        }
        return $subcategory;
    }
}