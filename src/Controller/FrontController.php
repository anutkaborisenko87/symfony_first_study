<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Video;
use App\Form\UserType;
use App\Repository\VideoRepository;
use App\Utils\CategoryTreeFrontPage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
class FrontController extends AbstractController
{
    /**
     * @Route("/", name="main_page")
     */
    public function index(): Response
    {
        return $this->render('front/index.html.twig');
    }

    /**
     * @Route ("/video-list/category/{categoryname}_{id}/{page}", defaults={"page": "1"}, name="video_list")
     */
    public function videoList(Category $category, int $page,
                              CategoryTreeFrontPage $categories,
                              Request $request,
                              EntityManagerInterface $entityManager): Response
    {
        $ids = $categories->getChildIds($category->getId());
        array_push($ids, $category->getId());
        $videos = $entityManager->getRepository(Video::class)->findByChildIds($ids, $page, $request->get('sortby'));
        $categories->getCategoryListAndParent($category->getId());
        return $this->render('front/video_list.html.twig', compact('categories', 'videos'));
    }

    /**
     * @Route ("/video-details/{video}", name="video_details")
     */
    public function videoDetails(VideoRepository $repository, $video): Response
    {
        $video = $repository->videoDetails($video);
        return $this->render('front/video_details.html.twig', compact('video'));
    }

    /**
     * @Route ("/search-results", methods={"GET"}, defaults={"page": "1"},
     *     name="search_results")
     */
    public function searchResults(int $page,
                                  Request $request,
                                  EntityManagerInterface $entityManager): Response
    {
        $videos = null;
        $query = $request->get('query');
        if ($query) {
            $videos = $entityManager->getRepository(Video::class)
                ->findByTitle($query, $page, $request->get('sortby'));
            if(!$videos->getItems()) $videos = null;
        }
        return $this->render('front/search_results.html.twig', compact('videos', 'query'));
    }

    /**
     * @Route ("/pricing", name="pricing")
     */
    public function pricing(): Response
    {
        return $this->render('front/pricing.html.twig');
    }

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
     * @Route ("/payment", name="payment")
     */
    public function payment(): Response
    {
        return $this->render('front/payment.html.twig');
    }

    public function mainCategories(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Category::class)->findBy(["parent" => null], ["name" => 'ASC']);
        return $this->render('front/_main_categories.html.twig', compact('categories'));
    }
    /**
     * @Route ("/logout", name="logout")
     */
    public function logout()
    {
       throw new \Exception('This should never be reached!');
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

    /**
     * @Route ("/new-comment/{video}", methods={"POST"}, name="new-comment")
     */

    public function newComment(Video $video, Request $request, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        if (!empty(trim($request->request->get('comment')))) {
            $comment = new Comment();
            $comment->setContent($request->request->get('comment'));
            $comment->setUser($this->getUser());
            $comment->setVideo($video);
            $entityManager->getRepository(Comment::class);
            $entityManager->persist($comment);
            $entityManager->flush();
        }
        return $this->redirectToRoute('video_details', ['video'=>$video->getId()]);
    }

    /**
     * @Route ("/video-list/{video}/like", name="like_video", methods={"POST"})
     * @Route ("/video-list/{video}/dislike", name="dislike_video", methods={"POST"})
     * @Route ("/video-list/{video}/unlike", name="undo_like_video", methods={"POST"})
     * @Route ("/video-list/{video}/undo_dislike", name="undo_dislike_video", methods={"POST"})
     */

    public function toggleLikesAjax(Video $video, Request $request, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        switch ($request->get('_route')) {
            case 'like_video':
                $result = $this->likeVideo($video, $entityManager);
                break;
            case 'dislike_video':
                $result = $this->dislikeVideo($video, $entityManager);
                break;
            case 'undo_like_video':
                $result = $this->unlikeVideo($video, $entityManager);
                break;
            case 'undo_dislike_video':
                $result = $this->undodislikeVideo($video, $entityManager);
                break;
        }
        return $this->json(['action' => $result, 'id'=>$video->getId()]);
    }

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
