<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-07 09:15:26
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-01-13 10:24:00
 */
 
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace diandi\admin\components;

use Yii;
use yii\base\BaseObject;
use yii\rbac\Assignment as RbacAssignment;

/**
 * Assignment represents an assignment of a role to a user.
 *
 * For more details and usage information on Assignment, see the [guide article on security authorization](guide:security-authorization).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Assignment extends RbacAssignment
{
    public $item_id;
    // 0:路由1：规则2：用户组
    public $parent_type;
    
    public $group_id;
    /**
     * @var string|int user ID (see [[\yii\web\User::id]])
     */
    public $userId;
    /**
     * @var string the role name
     */
    public $roleName;
    
    public $name;

    
    /**
     * @var int UNIX timestamp representing the assignment creation time
     */
    public $createdAt;
}
