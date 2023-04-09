<?php

namespace App\Controller;

use App\Entity\Subscription;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{

    /**
     * @Route ("/pricing", name="pricing")
     */
    public function pricing(): Response
    {
        $name = Subscription::getPlanDataNames();
        $price = Subscription::getPlanDataPrices();
        return $this->render('front/pricing.html.twig', compact('name', 'price'));
    }

}
