<?php

namespace App\Listeners;

use App\Entity\User;
use App\Entity\Video;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Swift_Mailer;
use Swift_SmtpTransport;
use Twig\Environment;

class NewVideoListener
{
    /**
     * @var Environment
     */
    private $templating;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(Environment $templating)
    {
        $transport = (new Swift_SmtpTransport('sandbox.smtp.mailtrap.io', 25))
            ->setUsername('05c6aabed8e1f3')
            ->setPassword('14d8548613c854')
        ;
        $this->templating = $templating;
        $this->mailer =  new Swift_Mailer($transport);

    }

    public function postPersist(LifecycleEventArgs $args)
    {

        $entity = $args->getObject();

        if (!$entity instanceof Video) {
            return;
        }


        $entityManager = $args->getObjectManager();
        $users = $entityManager->getRepository(User::class)->findAll();

        foreach($users as $user)
        {
            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('send@example.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->templating->render('emails/new_video.html.twig', [
                        'name' => $user->getName(),
                        'video' => $entity
                    ], 'text/html')
                );
            $this->mailer->send($message);
        }

    }

}