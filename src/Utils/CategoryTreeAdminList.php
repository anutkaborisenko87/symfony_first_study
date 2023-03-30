<?php

namespace App\Utils;

use App\Utils\AbstractClasses\CategoryTreeAbstractClass;

class CategoryTreeAdminList extends CategoryTreeAbstractClass
{
    /**
     * @var string
     */
    public $ul_open = '<ul class="fa-ul text-left">';

    /**
     * @var string
     */
    public $li_open = '<li><i class="fa-li fa fa-arrow-right"></i> ';

    /**
     * @var string
     */
    public $ahref_delete_open = ' <a onclick="return confirm(\'Are you sure?\')" href="';
    public $ahref_edit_open = ' <a href="';

    /**
     * @var string
     */    public $ahref_close = '"> ';

    /**
     * @var string
     */
    public $a_close = ' </a> ';

    /**
     * @var string
     */
    public $ul_close = '</ul>';

    /**
     * @var string
     */
    public $li_close = ' </li>';
    public function getCategoryList(array $categories_array): string
    {
        $this->categotyList .= $this->ul_open;
        foreach ($categories_array as $value) {
            $catName = $value['name'];
            $url_edit = $this->urlGenerator->generate('edit_category_admin_page', ['id' => $value['id']]);
            $url_delete = $this->urlGenerator->generate('delete_category_admin_page', ['id' => $value['id']]);
            $this->categotyList .= $this->li_open. $catName ;
            $this->categotyList .= $this->ahref_edit_open . $url_edit. $this->ahref_close . 'Edit' . $this->a_close;
            $this->categotyList .= $this->ahref_delete_open . $url_delete. $this->ahref_close . 'Delete' . $this->a_close;
            if (isset($value['children']) && !empty($value['children'])) {
                $this->getCategoryList($value['children']);
            }
            $this->categotyList .= $this->li_close;
        }
        $this->categotyList .= $this->ul_close;
        return $this->categotyList;

    }
}