<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    /**
     * @Route ("/register", name="register")
     */
    public function register(Request $request,
                             EntityManagerInterface $entityManager,
                             UserPasswordEncoderInterface $password_encoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $is_invalid = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->getRepository(User::class);
            $user->setName($request->request->get('user')['name']);
            $user->setLastName($request->request->get('user')['last_name']);
            $user->setEmail($request->request->get('user')['email']);
            $password = $password_encoder->encodePassword($user, $request->request->get('user')['password']['first']);
            $user->setPassword($password);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->loginUserAutomatically($user, $password);
            return $this->redirectToRoute('main_admin_page');
        } elseif ($request->isMethod('post')) {
            $is_invalid = 'is-invalid';
        }
        $form = $form->createView();
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
