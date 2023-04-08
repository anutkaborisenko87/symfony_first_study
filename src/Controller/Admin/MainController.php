<?php

namespace App\Controller\Admin;

use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class MainController extends AbstractController
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
     * @Route("/admin", name="main_admin_page")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }
    /**
     * @Route("/admin/videos", name="videos_admin_page")
     */
    public function videos(EntityManagerInterface $entityManager): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $videos = $entityManager->getRepository(Video::class)->findAll();
        } else {
            $videos  = $this->getUser()->getLikedVideos();
        }

        return $this->render('admin/videos.html.twig', compact('videos'));
    }

}
