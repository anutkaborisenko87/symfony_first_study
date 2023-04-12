<?php

namespace App\Controller\Admin\Superadmin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route ("/admin/su")
 */
class SuperAdminController extends AbstractController
{
    /**
     * @var Security
     */
    protected $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    /**
     * @Route("/upload-video", name="upload_video_admin_page")
     */
    public function upload_video()
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('main_admin_page');
        }
        return $this->render('admin/upload_video.html.twig');
    }

    /**
     * @Route("/users", name="users_admin_page")
     */
    public function users(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('main_admin_page');
        }

        $users = $userRepository->findAllExceptCurrentUser($this->getUser());
        return $this->render('admin/users.html.twig', compact('users'));
    }

    /**
     * @Route("/delete-user/{user}", name="delete_user")
     */
    public function deleteUser(User $user, EntityManagerInterface $entityManager): RedirectResponse
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('main_admin_page');
        }
        $entityManager->remove($user);
        $entityManager->flush();
        return $this->redirectToRoute('users_admin_page');
    }

}
