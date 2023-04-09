<?php

namespace App\Controller\Traits;

use App\Entity\Subscription;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

trait SaveSubscription
{
    private function saveSubscription(string $plan, $user, EntityManagerInterface $entityManager)
    {
        $date = (new DateTime())->modify('+1 month');
        $subscription = $user->getSubscription();
        if (is_null($subscription)) {
            $subscription = new Subscription();
        }
        if ($subscription->isFreePlanUsed() && $plan === Subscription::getPlanDataNameByIndex(0)) return;
        $subscription->setValidTo($date);
        $subscription->setPlan($plan);
        if ($plan === Subscription::getPlanDataNameByIndex(0)) {
            $subscription->setFreePlanUsed(true);
//            $subscription->setPaymentStatus('paid');
        }
        if (!$subscription->isFreePlanUsed()) {
            $subscription->setFreePlanUsed(false);
        }
        $subscription->setPaymentStatus('paid');
        $user->setSubscription($subscription);
        $entityManager->persist($user);
        $entityManager->flush();
    }

}