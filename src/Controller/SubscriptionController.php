<?php

namespace App\Controller;

use App\Controller\Traits\SaveSubscription;
use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    use SaveSubscription;
    /**
     * @Route ("/pricing", name="pricing")
     */
    public function pricing(): Response
    {
        $name = Subscription::getPlanDataNames();
        $price = Subscription::getPlanDataPrices();
        return $this->render('front/pricing.html.twig', compact('name', 'price'));
    }

    /**
     * @Route ("/payment/{paypal}", name="payment", defaults={"paypal":false})
     */
    public function payment($paypal, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        if ($paypal) {
            $this->saveSubscription($session->get('planName'), $this->getUser(), $entityManager);
            return $this->redirectToRoute('main_admin_page');
        }
        return $this->render('front/payment.html.twig');
    }

}