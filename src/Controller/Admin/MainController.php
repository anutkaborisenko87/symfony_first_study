<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Video;
use DateTime;
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
        $subscription = $this->getUser()->getSubscription();
        return $this->render('admin/my_profile.html.twig', compact('subscription'));
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
    /**
     * @Route ("/admin/cancel_plan", name="cancel_plan")
     */
    public function cancelPlan(EntityManagerInterface $entityManager)
    {
        $user = $entityManager->getRepository(User::class)->find($this->getUser());
        $subscription = $user->getSubscription();
        $subscription->setValidTo(new DateTime());
        $subscription->setPaymentStatus(null);
        $subscription->setPlan('canceled');
        $entityManager->persist($user);
        $entityManager->persist($subscription);
        $entityManager->flush();
        return $this->redirectToRoute('main_admin_page');
    }

}
