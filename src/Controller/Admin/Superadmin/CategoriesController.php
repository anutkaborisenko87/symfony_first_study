<?php

namespace App\Controller\Admin\Superadmin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Utils\CategoryTreeAdminList;
use App\Utils\CategoryTreeAdminOptionList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route ("/admin/su")
 */
class CategoriesController extends AbstractController
{
    /**
     *
     * @var Security
     */
    protected $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    /**
     * @Route("/categories", name="categories_admin_page", methods={"GET", "POST"})
     */
    public function categories(CategoryTreeAdminList $categories,
                               Request $request,
                               EntityManagerInterface $entityManager): Response
    {
        $categoriesList = $categories->getCategoryList($categories->buildTree());
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $is_invalid = null;

        if ($this->saveCategory($form, $request, $category, $entityManager)) {

            return $this->redirectToRoute('categories_admin_page');

        } elseif ($request->isMethod('post')) {
            $is_invalid = 'is-invalid';
        }
        $form = $form->createView();
        return $this->render('admin/categories.html.twig', compact('categoriesList', 'form', 'is_invalid'));
    }

    /**
     * @Route("/edit_category/{id}", name="edit_category_admin_page", methods={"GET", "POST"})
     */
    public function editCategory(Category $category,
                                 Request $request,
                                 EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $is_invalid = null;
        if ($this->saveCategory($form, $request, $category, $entityManager)) {

            return $this->redirectToRoute('categories_admin_page');

        } elseif ($request->isMethod('post')) {
            $is_invalid = 'is-invalid';
        }
        $form = $form->createView();
        return $this->render('admin/edit_category.html.twig', compact('category', 'form', 'is_invalid'));
    }

    /**
     * @Route("/delete_category/{id}", name="delete_category_admin_page")
     */
    public function deleteCategory(Category $category, EntityManagerInterface $entityManager): RedirectResponse
    {
        if (!$this->security->isGranted('IS_AUTHENTICATED_FULLY') && !$this->security->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('login');
        }
        $entityManager->remove($category);
        $entityManager->flush();
        return $this->redirectToRoute('categories_admin_page');
    }

    public function getAllCategories(CategoryTreeAdminOptionList $categories, $editedCategory = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $categories->getCategoryList($categories->buildTree());
        return $this->render('admin/_all_categories.html.twig', compact('categories', 'editedCategory'));
    }

    private function saveCategory($form, $request, $category, $entityManager): bool
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category->setName($request->request->get('category')['name']);
            $repository = $entityManager->getRepository(Category::class);
            $parent = $repository->find($request->request->get('category')['parent']);
            $category->setParent($parent);
            $entityManager->persist($category);
            $entityManager->flush();
            return true;
        }
        return false;
    }

}
