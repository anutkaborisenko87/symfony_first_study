<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;
use App\Entity\Video;
use Exception;

class VideosFixtures extends Fixture
{
    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->VideoData() as [$title, $path, $category_id]) {
            $duration = random_int(10, 300);
            $category = $manager->getRepository(Category::class)->find($category_id);
            $video = new Video();
            $video->setTitle($title);
            $video->setPath('https://player.vimeo.com/video/'.$path);
            $video->setCategory($category);
            $video->setDuration($duration);
            $manager->persist($video);
        }
        $manager->flush();
        $this->loadLikes($manager);
        $this->loadDisLikes($manager);
    }

    private function VideoData(): array
    {
        return [

            ['Movies 1',289729765,4],
            ['Movies 2',238902809,4],
            ['Movies 3',150870038,4],
            ['Movies 4',219727723,4],
            ['Movies 5',289879647,4],
            ['Movies 6',261379936,4],
            ['Movies 7',289029793,4],
            ['Movies 8',60594348,4],
            ['Movies 9',290253648,4],

            ['Family 1',289729765,17],
            ['Family 2',289729765,17],
            ['Family 3',289729765,17],

            ['Romantic comedy 1',289729765,19],
            ['Romantic comedy 2',289729765,19],

            ['Romantic drama 1',289729765,20],

            ['Toys  1',289729765,2],
            ['Toys  2',289729765,2],
            ['Toys  3',289729765,2],
            ['Toys  4',289729765,2],
            ['Toys  5',289729765,2],
            ['Toys  6',289729765,2]

        ];
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function loadLikes(ObjectManager $manager)
    {
        foreach ($this->likesData() as [$video_id, $user_id]) {
            $video = $manager->getRepository(Video::class)->find($video_id);
            $user = $manager->getRepository(User::class)->find($user_id);
            $video->addUsersThatLike($user);
            $manager->persist($video);
        }
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function loadDisLikes(ObjectManager $manager)
    {
        foreach ($this->dislikesData() as [$video_id, $user_id]) {
            $video = $manager->getRepository(Video::class)->find($video_id);
            $user = $manager->getRepository(User::class)->find($user_id);
            $video->addUsersThatDontLike($user);
            $manager->persist($video);
        }
        $manager->flush();
    }

    private function likesData(): array
    {
        return [
            [10, 1],
            [10, 2],
            [10, 3],

            [11, 1],
            [11, 2],
            [11, 3],

            [8, 1],
            [8, 2],
            [8, 3],
        ];
    }

    private function dislikesData(): array
    {
        return [
          [2, 1],
          [1, 2],
          [3, 3],
        ];
    }
}
