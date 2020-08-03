<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-06 15:25:48
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-19 06:39:50
 */

namespace diandi\admin\models;

use diandi\admin\components\Configs;
use diandi\admin\components\Helper;
use Yii;

/**
 * Description of Assignment.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 *
 * @since 2.5
 */
class Assignment extends \diandi\admin\BaseObject
{
    /**
     * @var int User id
     */
    public $id;
    /**
     * @var \yii\web\IdentityInterface User
     */
    public $user;

    public $type;

    /**
     * {@inheritdoc}
     */
    public function __construct($item, $user = null, $config = array())
    {
        if (isset($item['id'])) {
            $this->id = $item['id'];
        }
        
        if (isset($item['type'])) {
            $this->type = $item['type'];
        }
        
        $this->user = $user;
        parent::__construct($config);
    }

    /**
     * Grands a roles from a user.
     *
     * @param array $items
     *
     * @return int number of successful grand
     */
    public function assign($items)
    {
        $manager = Configs::authManager();
        $success = 0;
        if (!empty($items['group'])) {
            foreach ($items['group'] as $name) {
                try {
                    $item = $manager->getGroup($name, $this->type);

                    $item = $item ?: $manager->getGroupPermission($name);

                    $manager->assignGroup($item, $this->id);
                    ++$success;
                } catch (\Exception $exc) {
                    Yii::$app->session->setFlash('error', $exc->getMessage());
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }

        if (!empty($items['permission'])) {
            foreach ($items['permission'] as $name) {
                try {
                    $item = $manager->getRole($name);
                    $item = $item ?: $manager->getPermission($name);
                
                    $manager->assign($item, $this->id);
                    ++$success;
                } catch (\Exception $exc) {
                    p($exc->getMessage());
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }

        Helper::invalidate();

        return $success;
    }

    /**
     * Revokes a roles from a user.
     *
     * @param array $items
     *
     * @return int number of successful revoke
     */
    public function revoke($items)
    {
        $manager = Configs::authManager();
        $success = 0;

        if (isset($items['group'])) {
            foreach ($items['group'] as $name) {
                try {
                    $item = $manager->getGroup($name, $this->type);
                    $item = $item ?: $manager->getGroupPermission($name);
                    $manager->revokeGroup($item, $this->id);
                    ++$success;
                } catch (\Exception $exc) {
                    p($exc->getMessage());
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }

        if (isset($items['permission'])) {
            foreach ($items['permission'] as $name) {
                try {
                    $item = $manager->getRole($name);
                    $item = $item ?: $manager->getPermission($name);
                    $manager->revoke($item, $this->id);
                    ++$success;
                } catch (\Exception $exc) {
                    p($exc->getMessage());
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }

        Helper::invalidate();

        return $success;
    }

    /**
     * Get all available and assigned roles/permission.
     *
     * @return array
     */
    public function getItems($type = 0)
    {
        $manager = Configs::authManager();
        $available = [];
        // 用户组授权
        foreach (array_keys($manager->getGroups($type)) as $name) {
            $available[$name] = 'role';
        }

        foreach (array_keys($manager->getPermissions($type)) as $name) {
            if ($name[0] != '/') {
                $available[$name] = 'permission';
            }
        }

        // 路由授权
        foreach (array_keys($manager->getRoutes($type)) as $name) {
            $available[$name] = 'route';
        }

        $group = AuthAssignmentGroup::findAll(['user_id' => $this->id]);

        $assigned = [];
        foreach ($group as $key => $item) {
            $assigned[$item->item_name] = 'role';
            unset($available[$item->item_name]);
        }
        $assignmentsType = [
            0 => 'route',
            1 => 'permission',
            2 => 'role',
        ];
        foreach ($manager->getAssignments($this->id) as $item) {
            $assigned[$item->roleName] = $assignmentsType[$item->parent_type];
            unset($available[$item->roleName]);
        }

        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if ($this->user) {
            return $this->user->$name;
        }
    }
}
