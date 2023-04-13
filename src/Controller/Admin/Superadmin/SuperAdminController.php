<?php

namespace App\Controller\Admin\Superadmin;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\Video;
use App\Form\VideoType;
use App\Repository\UserRepository;
use App\Utils\Inertfaces\UploaderInterface;
use App\Utils\LocalUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route ("/admin/su")
 */
class SuperAdminController extends AbstractController
{
    /**
     * @var Security
     */
    protected $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    /**
     * @Route("/upload-video", name="upload_video_admin_page")
     */
    public function uploadVideo()
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('main_admin_page');
        }
        return $this->render('admin/upload_video.html.twig');
    }
    /**
     * @Route("/upload-video-localy", name="upload_video_locally_admin_page")
     */
    public function uploadVideoLocally(Request $request,
                                       LocalUploader $fileUploader,
                                       EntityManagerInterface $entityManager)
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('main_admin_page');
        }
        $video = new Video();
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $video->getUploadedVideo();
            $fileName = $fileUploader->upload($file);

            $basePath = Video::uploadFolder;
            $video->setPath($basePath.$fileName[0]);
            $video->setTitle($fileName[1]);
            $entityManager->persist($video);
            $entityManager->flush();
            return $this->redirectToRoute('videos_admin_page');
        }
        $form = $form->createView();
        return $this->render('admin/upload_video_localy.html.twig', compact('form'));
    }

    /**
     * @Route("/users", name="users_admin_page")
     */
    public function users(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('main_admin_page');
        }

        $users = $userRepository->findAllExceptCurrentUser($this->getUser());
        return $this->render('admin/users.html.twig', compact('users'));
    }

    /**
     * @Route("/delete-user/{user}", name="delete_user")
     */
    public function deleteUser(User $user, EntityManagerInterface $entityManager): RedirectResponse
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('main_admin_page');
        }
        $entityManager->remove($user);
        $entityManager->flush();
        return $this->redirectToRoute('users_admin_page');
    }

    /**
     * @Route("/delete-video/{video}", name="delete_video")
     */

    public function deleteVideo(Video $video, UploaderInterface $fileUploader, EntityManagerInterface $entityManager): RedirectResponse
    {
        $path = $video->getPath();
        $entityManager->remove($video);
        $entityManager->flush();
        if ($fileUploader->delete($path)) {
            $this->addFlash('success', 'The video was successfully deleted');
        } else {
            $this->addFlash('danger', 'We were not able to delete. Check the video.');
        }
        return $this->redirectToRoute('videos_admin_page');
    }

    /**
     * @Route ("/update-video-category/{video}", methods={"POST"}, name="update_video_category")
     */
    public function updateVideoCategory(Video $video, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $category = $entityManager->getRepository(Category::class)->find($request->request->get('video_category'));
        $video->setCategory($category);
        $entityManager->persist($video);
        $entityManager->flush();
        return $this->redirectToRoute('videos_admin_page');
    }

}
