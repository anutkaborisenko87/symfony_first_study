<?php

namespace App\Utils;

use App\Entity\Video;
use DateTime;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class VideoForNotValidSubscription
{
    public $isSubscriptionValid = false;

    public function __construct(TokenStorageInterface   $tokenStorage)
    {
        $token = $tokenStorage->getToken();
        if ($token !== null && $token->isAuthenticated()) {
            $user = $token->getUser();
            if (!is_null($user->getSubscription())) {
                $payment_status =$user->getSubscription()->getPaymentStatus();
                $valid = new DateTime() < $user->getSubscription()->getValidTo();
                if (!is_null($payment_status) && $valid) {
                    $this->isSubscriptionValid = true;
                }
            }
        }

    }

    public function check(): ?string
    {
        if ($this->isSubscriptionValid) {
            return null;
        } else {
            return Video::videoForNotLoggedInOrNoMembers;
        }
    }

}