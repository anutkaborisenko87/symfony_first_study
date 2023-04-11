<?php

namespace App\Controller\Admin\Superadmin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
    public function users()
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('main_admin_page');
        }
        return $this->render('admin/users.html.twig');
    }

}
