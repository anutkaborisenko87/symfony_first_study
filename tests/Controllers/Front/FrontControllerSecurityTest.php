<?php

namespace App\Tests\Controllers\Front;

use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontControllerSecurityTest extends WebTestCase
{
    /**
     * @dataProvider getSecureUrl
     * @param string $url
     * @return void
     */
    public function testSecureUrls(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);
        $response = $client->getResponse();
        $this->assertStringContainsString('/login', $response->getTargetUrl());

    }

    public function getSecureUrl(): Generator
    {
        yield ['/admin'];
        yield ['/admin/videos'];
        yield ['/admin/su/categories'];
        yield ['/admin/su/delete_category/1'];
        yield ['/admin/su/edit_category/1'];
    }
    public function testVideoForMembersOnly()
    {
        $client = static::createClient();
        $authenticationUtils = $client->getContainer()->get('security.token_storage');
        $token = $authenticationUtils->getToken();
        if ($token) {
            $authenticationUtils->setToken(null);
        }
        $client->request('GET', '/video-list/category/movies_4');
        $this->assertStringContainsString('Video for <b>MEMBERS</b> only.', $client->getResponse()->getContent());
    }
}
