<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Video;
use App\Form\UserType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route ("/admin")
 */
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
     * @Route("/", name="main_admin_page")
     */
    public function index(Request $request, UserPasswordHasherInterface $password_encoder, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($this->getUser());
        $form = $this->createForm(UserType::class, $user, compact('user'));
        $form->handleRequest($request);
        $is_invalid = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setName($request->request->get('user')['name']);
            $user->setLastName($request->request->get('user')['last_name']);
            $user->setEmail($request->request->get('user')['email']);

            $password = $password_encoder->hashPassword($user, $request->request->get('user')['password']['first']);
            $user->setPassword($password);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Your changes were saved!'
            );
        } elseif ($request->isMethod('post')) {
            $is_invalid = 'is-invalid';
        }
        $form = $form->createView();
        $subscription = $this->getUser()->getSubscription();
        return $this->render('admin/my_profile.html.twig', compact('subscription', 'form', 'is_invalid'));
    }
    /**
     * @Route("/videos", name="videos_admin_page")
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
     * @Route ("/cancel_plan", name="cancel_plan")
     */
    public function cancelPlan(EntityManagerInterface $entityManager): RedirectResponse
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

    /**
     * @Route ("/delete_account", name="delete_account")
     */

    public function deleteAccount(EntityManagerInterface $entityManager): RedirectResponse
    {
        $user = $entityManager->getRepository(User::class)->find($this->getUser());
        $entityManager->remove($user);
        $entityManager->flush();
        session_destroy();
        return $this->redirectToRoute('main_page');
    }
}
