<?php

namespace App\Utils;

use App\Utils\AbstractClasses\CategoryTreeAbstractClass;

class CategoryTreeAdminOptionList extends CategoryTreeAbstractClass
{

    public function getCategoryList(array $categories_array, int $repeat = 0)
    {
        foreach ($categories_array as $value) {
            $this->categotyList[] = ['name'=> str_repeat("-", $repeat).$value["name"], 'id'=>$value['id']];
            if (isset($value['children']) && !empty($value['children'])) {
                $repeat = $repeat + 2;
                $this->getCategoryList($value['children'], $repeat);
                $repeat = $repeat - 2;
            }
        }

        return $this->categotyList;
    }
}