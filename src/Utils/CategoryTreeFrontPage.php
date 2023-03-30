<?php

namespace App\Utils;

use App\Twig\AppExtension;
use App\Utils\AbstractClasses\CategoryTreeAbstractClass;

class CategoryTreeFrontPage extends CategoryTreeAbstractClass
{
    /**
     * @var string
     */
    public $ul_open = '<ul>';

    /**
     * @var string
     */
    public $li_open = '<li>';

    /**
     * @var string
     */
    public $ahref_open = '<a href="';

    /**
     * @var string
     */    public $ahref_close = '">';

    /**
     * @var string
     */
    public $a_close = '</a>';

    /**
     * @var string
     */
    public $ul_close = '</ul>';

    /**
     * @var string
     */
    public $li_close = '</li>';
    /**
     * @var AppExtension
     */
    public $slugger;
    /**
     * @var mixed
     */
    public $mainParentName;
    /**
     * @var mixed
     */
    public $mainParentId;
    /**
     * @var mixed
     */
    public $currentCategoryName;

    /**
     * @param array $categories_array
     * @return string
     */
    public function getCategoryList(array $categories_array): string
    {
        $this->categotyList .= $this->ul_open;
        foreach ($categories_array as $value) {
            $catName = $value['name'];
            $url = $this->urlGenerator->generate('video_list', ['categoryname' => $this->slugger->slugify($catName), 'id' => $value['id']]);
            $this->categotyList .= $this->li_open;
            $this->categotyList .= $this->ahref_open . $url . $this->ahref_close . $catName . $this->a_close;
            if (isset($value['children']) && !empty($value['children'])) {
                $this->getCategoryList($value['children']);
            }
            $this->categotyList .= $this->li_close;
        }
        $this->categotyList .= $this->ul_close;
        return $this->categotyList;
    }

    /**
     * @param int $id
     * @return array
     */
    public function getMainparent(int $id): array
    {
        $key = array_search($id, array_column($this->categotiesArrayFromDb, 'id'));
        if (!is_null($this->categotiesArrayFromDb[$key]['parent_id'])) {
            return $this->getMainparent($this->categotiesArrayFromDb[$key]['parent_id']);
        } else {
            return [
                'id' => $this->categotiesArrayFromDb[$key]['id'],
                'name' => $this->categotiesArrayFromDb[$key]['name']
            ];
        }
    }

    /**
     * @param int $id
     * @return string
     */
    public function getCategoryListAndParent(int $id): string
    {
        $this->slugger = new AppExtension();
        $parentData = $this->getMainparent($id);
        $this->mainParentName = $parentData['name'];
        $this->mainParentId = $parentData['id'];
        $key = array_search($id, array_column($this->categotiesArrayFromDb, 'id'));
        $this->currentCategoryName = $this->categotiesArrayFromDb[$key]['name'];
        $categories_array = $this->buildTree($parentData['id']);
        return $this->getCategoryList($categories_array);
    }
}