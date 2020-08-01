<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-04 07:41:27
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-08-01 10:18:49
 */
 
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace diandi\admin\components;


/**
 * For more details and usage information on Permission, see the [guide article on security authorization](guide:security-authorization).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Permission extends Item
{
    /**
     * {@inheritdoc}
     */
    public $type = self::TYPE_PERMISSION;

    public $parent_type = 1;
    
    public $id;

    
    
    public $module_name = 'sys';
    
}
