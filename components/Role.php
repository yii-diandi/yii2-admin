<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-04 07:40:56
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-07 10:35:49
 */
 
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace diandi\admin\components;

use yii\rbac\Role as RbacRole;

/**
 * For more details and usage information on Role, see the [guide article on security authorization](guide:security-authorization).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Role extends item
{
    public $module_name;
    /**
     * {@inheritdoc}
     */
    public $type = self::TYPE_PERMISSION;

    public $parent_type = 2;

}
