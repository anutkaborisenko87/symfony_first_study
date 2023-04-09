<?php

namespace App\Controller;

use App\Controller\Traits\SaveSubscription;
use App\Entity\Subscription;
use App\Entity\User;
use App\Form\UserType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    use SaveSubscription;

    /**
     * @Route ("/register/{plan}", name="register")
     */
    public function register(Request $request,
                             EntityManagerInterface $entityManager,
                             UserPasswordHasherInterface $password_encoder,
                             SessionInterface $session, $plan
    )
    {
        if ($request->isMethod('GET')) {
            $session->set('planName', $plan);
            $session->set('planPrice', Subscription::getPlanDataPriceByIndex($plan));
        }
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $is_invalid = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->getRepository(User::class);
            $user->setName($request->request->get('user')['name']);
            $user->setLastName($request->request->get('user')['last_name']);
            $user->setEmail($request->request->get('user')['email']);
            $password = $password_encoder->hashPassword($user, $request->request->get('user')['password']['first']);
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);
            $this->saveSubscription($plan, $user, $entityManager);
            $this->loginUserAutomatically($user, $password);
            return $this->redirectToRoute('main_admin_page');
        } elseif ($request->isMethod('post')) {
            $is_invalid = 'is-invalid';
        }
        $form = $form->createView();
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED') && $plan === Subscription::getPlanDataNameByIndex(0)) {
            $this->saveSubscription($plan, $this->getUser(), $entityManager);
            return $this->redirectToRoute('main_admin_page');
        } elseif ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('payment');
        }
        return $this->render('front/register.html.twig', compact('form', 'is_invalid'));
    }

    /**
     * @Route ("/login", name="login")
     */
    public function login(AuthenticationUtils $helper): Response
    {
        $error = $helper->getLastAuthenticationError();
        return $this->render('front/login.html.twig', compact('error'));
    }

    /**
     * @Route ("/logout", name="logout")
     * @throws Exception
     */
    public function logout()
    {
        throw new Exception('This should never be reached!');
    }

    private function loginUserAutomatically(User $user, $password)
    {
        $token = new UsernamePasswordToken(
            $user,
            $password,
            'main',
            $user->getRoles()
        );
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));
    }

}
