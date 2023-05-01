<?php

namespace App\Controller;

use App\Controller\Traits\Likes;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Video;
use App\Repository\VideoRepository;
use App\Utils\CategoryTreeFrontPage;
use App\Utils\VideoForNotValidSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    use Likes;
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
                              VideoForNotValidSubscription $forNotValidSubscription,
                              EntityManagerInterface $entityManager): Response
    {
        $ids = $categories->getChildIds($category->getId());
        array_push($ids, $category->getId());
        $videos = $entityManager->getRepository(Video::class)->findByChildIds($ids, $page, $request->get('sortby'));
        $categories->getCategoryListAndParent($category->getId());
        $video_no_members = $forNotValidSubscription->check();
        return $this->render('front/video_list.html.twig', compact('categories', 'videos', 'video_no_members'));
    }

    /**
     * @Route ("/video-details/{video}", name="video_details")
     */
    public function videoDetails(VideoRepository $repository, $video, VideoForNotValidSubscription $forNotValidSubscription): Response
    {
        $video_no_members = $forNotValidSubscription->check();
        $video = $repository->videoDetails($video);
        return $this->render('front/video_details.html.twig', compact('video', 'video_no_members'));
    }

    /**
     * @Route ("/search-results", methods={"GET"}, defaults={"page": "1"},
     *     name="search_results")
     */
    public function searchResults(int $page,
                                  Request $request,
                                  VideoForNotValidSubscription $forNotValidSubscription,
                                  EntityManagerInterface $entityManager): Response
    {
        $video_no_members = $forNotValidSubscription->check();
        $videos = null;
        $query = $request->get('query');
        if ($query) {
            $videos = $entityManager->getRepository(Video::class)
                ->findByTitle($query, $page, $request->get('sortby'));
            if(!$videos->getItems()) $videos = null;
        }
        return $this->render('front/search_results.html.twig', compact('videos', 'query', 'video_no_members'));
    }

    public function mainCategories(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Category::class)->findBy(["parent" => null], ["name" => 'ASC']);
        return $this->render('front/_main_categories.html.twig', compact('categories'));
    }

    /**
     * @Route ("/new-comment/{video}", methods={"POST"}, name="new-comment")
     */

    public function newComment(Video $video, Request $request, EntityManagerInterface $entityManager): RedirectResponse
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

    public function toggleLikesAjax(Video $video, Request $request, EntityManagerInterface $entityManager): JsonResponse
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

    /**
     * @Route("/dalete-comment/{comment}", name="delete_comment")
     * @Security ("user.getId() == comment.getUser().getId()")
     */
    public function deleteComment(Comment $comment, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $entityManager->remove($comment);
        $entityManager->flush();
        return $this->redirect($request->headers->get('referer'));
    }

}
