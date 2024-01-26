<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-03 18:38:59
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-01-13 11:18:00
 */

/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

 
namespace diandi\admin\components;

use yii\base\BaseObject;

/**
 * For more details and usage information on Item, see the [guide article on security authorization](guide:security-authorization).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 */
class Route extends BaseObject
{
    const TYPE_ROLE = 0;
    
    const TYPE_MODULE = 1;
    
    const MODULE_NAME = 'sys';
    
    public $id;
    
    public $item_id;
    
    public $pid;
    
    public $module_name;
   
    
    public $is_sys;
    
    public $child_type;

    public $route_type;

    
    public $parent_type;
    /**
     * @var string the name of the item. This must be globally unique.
     */
    public $title;
    public $name;
    /**
     * @var string the item description
     */
    public $description;
    
    /**
     * @var mixed the additional data associated with this item
     */
    public $data;
    /**
     * @var int UNIX timestamp representing the item creation time
     */
    public $createdAt;
    /**
     * @var int UNIX timestamp representing the item updating time
     */
    public $updatedAt;

    
}
