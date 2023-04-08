<?php

namespace App\Controller\Admin\Superadmin;

use App\Controller\Admin\MainController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;


class SuperAdminController extends MainController
{
    /**
     * @Route("admin/su/upload-video", name="upload_video_admin_page")
     */
    public function upload_video()
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('main_admin_page');
        }
        return $this->render('admin/upload_video.html.twig');
    }

    /**
     * @Route("admin/su/users", name="users_admin_page")
     */
    public function users()
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('main_admin_page');
        }
        return $this->render('admin/users.html.twig');
    }

}
