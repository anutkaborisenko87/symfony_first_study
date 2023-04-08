<?php

namespace App\Controller\Traits;

use App\Entity\User;
use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;

trait Likes
{
    private function likeVideo(Video $video, EntityManagerInterface $entityManager): string
    {
        $user = $entityManager->getRepository(User::class)->find($this->getUser());
        $user->addLikedVideo($video);
        $entityManager->persist($user);
        $entityManager->flush();
        return 'liked';
    }

    private function dislikeVideo(Video $video, EntityManagerInterface $entityManager): string
    {
        $user = $entityManager->getRepository(User::class)->find($this->getUser());
        $user->addDisLikeVideo($video);
        $entityManager->persist($user);
        $entityManager->flush();
        return 'disliked';
    }

    private function unlikeVideo(Video $video, EntityManagerInterface $entityManager): string
    {
        $user = $entityManager->getRepository(User::class)->find($this->getUser());
        $user->removeLikedVideo($video);
        $entityManager->persist($user);
        $entityManager->flush();
        return 'undo liked';
    }

    private function undodislikeVideo(Video $video, EntityManagerInterface $entityManager): string
    {
        $user = $entityManager->getRepository(User::class)->find($this->getUser());
        $user->removeDisLikeVideo($video);
        $entityManager->persist($user);
        $entityManager->flush();
        return 'undo disliked';
    }


}