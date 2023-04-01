<?php

namespace App\Tests\Utils;

use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Twig\AppExtension;

class CategoryTest extends KernelTestCase
{
    protected $mockedCategoryTreeFrontPage;
    protected $mockedCategoryTreeAdminList;
    protected $mockedCategoryTreeAdminOptionList;

    public function dataForCategoryTreeFrontPage(): Generator
    {
        yield [
            '<ul><li><a href="/video-list/category/computers_6">Computers</a><ul><li><a href="/video-list/category/laptops_8">Laptops</a><ul><li><a href="/video-list/category/hp_14">HP</a></li></ul></li></ul></li></ul>',
            [
                ['name'=>'Electronics','id'=>1, 'parent_id'=>null],
                ['name'=>'Computers','id'=>6, 'parent_id'=>1],
                ['name'=>'Laptops','id'=>8, 'parent_id'=>6],
                ['name'=>'HP','id'=>14, 'parent_id'=>8]
            ],
            1
        ];

        yield [
            '<ul><li><a href="/video-list/category/computers_6">Computers</a><ul><li><a href="/video-list/category/laptops_8">Laptops</a><ul><li><a href="/video-list/category/hp_14">HP</a></li></ul></li></ul></li></ul>',
            [
                ['name'=>'Electronics','id'=>1, 'parent_id'=>null],
                ['name'=>'Computers','id'=>6, 'parent_id'=>1],
                ['name'=>'Laptops','id'=>8, 'parent_id'=>6],
                ['name'=>'HP','id'=>14, 'parent_id'=>8]
            ],
            6
        ];

        yield [
            '<ul><li><a href="/video-list/category/computers_6">Computers</a><ul><li><a href="/video-list/category/laptops_8">Laptops</a><ul><li><a href="/video-list/category/hp_14">HP</a></li></ul></li></ul></li></ul>',
            [
                ['name'=>'Electronics','id'=>1, 'parent_id'=>null],
                ['name'=>'Computers','id'=>6, 'parent_id'=>1],
                ['name'=>'Laptops','id'=>8, 'parent_id'=>6],
                ['name'=>'HP','id'=>14, 'parent_id'=>8]
            ],
            8
        ];

        yield [
            '<ul><li><a href="/video-list/category/computers_6">Computers</a><ul><li><a href="/video-list/category/laptops_8">Laptops</a><ul><li><a href="/video-list/category/hp_14">HP</a></li></ul></li></ul></li></ul>',
            [
                ['name'=>'Electronics','id'=> 1, 'parent_id'=>null],
                ['name'=>'Computers','id'=> 6, 'parent_id'=> 1],
                ['name'=>'Laptops','id'=> 8, 'parent_id'=> 6],
                ['name'=>'HP','id'=> 14, 'parent_id'=> 8]
            ],
            14
        ];
    }

    public function dataForCategoryTreeAdminOptionList(): Generator
    {
        yield [
            [
                ['name'=>'Electronics','id'=> 1],
                ['name'=>'--Computers','id'=> 6],
                ['name'=>'----Laptops','id'=> 8],
                ['name'=>'------HP','id'=> 14]
            ],
            [
                ['name'=>'Electronics','id'=> 1, 'parent_id'=>null],
                ['name'=>'Computers','id'=> 6, 'parent_id'=> 1],
                ['name'=>'Laptops','id'=> 8, 'parent_id'=> 6],
                ['name'=>'HP','id'=> 14, 'parent_id'=> 8]
            ]
        ];
    }

    public function dataForCategoryTreeAdminList(): Generator
    {
        yield [
            '<ul class="fa-ul text-left"><li><i class="fa-li fa fa-arrow-right"></i> Electronics <a href="/admin/su/edit_category/1"> Edit </a>  <a onclick="return confirm(\'Are you sure?\')" href="/admin/su/delete_category/1"> Delete </a> <ul class="fa-ul text-left"><li><i class="fa-li fa fa-arrow-right"></i> Computers <a href="/admin/su/edit_category/6"> Edit </a>  <a onclick="return confirm(\'Are you sure?\')" href="/admin/su/delete_category/6"> Delete </a> <ul class="fa-ul text-left"><li><i class="fa-li fa fa-arrow-right"></i> Laptops <a href="/admin/su/edit_category/8"> Edit </a>  <a onclick="return confirm(\'Are you sure?\')" href="/admin/su/delete_category/8"> Delete </a> <ul class="fa-ul text-left"><li><i class="fa-li fa fa-arrow-right"></i> HP <a href="/admin/su/edit_category/14"> Edit </a>  <a onclick="return confirm(\'Are you sure?\')" href="/admin/su/delete_category/14"> Delete </a>  </li></ul> </li></ul> </li></ul> </li></ul>',
            [
                ['name'=>'Electronics','id'=> 1, 'parent_id'=>null],
                ['name'=>'Computers','id'=> 6, 'parent_id'=> 1],
                ['name'=>'Laptops','id'=> 8, 'parent_id'=> 6],
                ['name'=>'HP','id'=> 14, 'parent_id'=> 8]
            ]
        ];
    }

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $urlgenerator = $kernel->getContainer()->get('router');
        $tested_classes = [
            'CategoryTreeFrontPage',
            'CategoryTreeAdminOptionList',
            'CategoryTreeAdminList'
        ];
        foreach ($tested_classes as $class) {
            $name = 'mocked'.$class;

            $this->$name = $this->getMockBuilder('App\\Utils\\'.$class)
                ->disableOriginalConstructor()->setMethods()->getMock();
            $this->$name->urlGenerator = $urlgenerator;
        }


    }

    /**
     * @dataProvider dataForCategoryTreeFrontPage
     * @param $string
     * @param $array
     * @param $id
     * @return void
     */
    public function testCategoryTreeFrontPage($string, $array, $id):void
    {
        $this->mockedCategoryTreeFrontPage->slugger = new AppExtension;
        $this->mockedCategoryTreeFrontPage->categotiesArrayFromDb = $array;
        $main_parent_id = $this->mockedCategoryTreeFrontPage->getMainParent($id)['id'];
        $array_new = $this->mockedCategoryTreeFrontPage->buildTree($main_parent_id);
        $this->assertSame($string, $this->mockedCategoryTreeFrontPage->getCategoryList($array_new));
    }

    /**
     *
     * @dataProvider dataForCategoryTreeAdminOptionList
     * @param array $arrayToCompare
     * @param array $arrayFromDb
     * @return void
     */

    public function testCategoryTreeAdminOptionList(array $arrayToCompare, array $arrayFromDb)
    {
        $this->mockedCategoryTreeAdminOptionList->categotiesArrayFromDb = $arrayFromDb;
        $arrayFromDb = $this->mockedCategoryTreeAdminOptionList->buildTree();
        $this->assertSame($arrayToCompare, $this->mockedCategoryTreeAdminOptionList->getCategoryList($arrayFromDb));
    }

    /**
     * @dataProvider dataForCategoryTreeAdminList
     * @param string $string
     * @param array $array
     * @return void
     */
    public function testCategoryTreeAdminList(string $string, array $array)
    {
        $this->mockedCategoryTreeAdminList->categotiesArrayFromDb = $array;
        $array = $this->mockedCategoryTreeAdminList->buildTree();
        $this->assertSame($string, $this->mockedCategoryTreeAdminList->getCategoryList($array));

    }
}
