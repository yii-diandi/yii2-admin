<?php

/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-06 15:25:48
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-01-24 00:47:00
 */

namespace diandi\admin\models;

use diandi\admin\components\Configs;
use diandi\admin\components\Helper;
use Yii;
use yii\base\ErrorException;

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

    public $is_sys;

    /**
     * {@inheritdoc}
     */
    public function __construct($item, $user = null, $config = array())
    {
        if (isset($item['id'])) {
            $this->id = $item['id'];
        }

        if (isset($item['is_sys'])) {
            $this->is_sys = $item['is_sys'];
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
        
        if (!empty($items['role'])) {
            foreach ($items['role'] as $name) {
                try {
                    $item = $manager->getGroup($name, $this->is_sys);
                    $item = $item ?: $manager->getGroupPermission($name);
                    $manager->assignGroup($item, $this->id);
                    ++$success;
                } catch (\Exception $exc) {
                    Yii::$app->session->setFlash('error', $exc->getMessage());
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }


        if (!empty($items['group'])) {
            foreach ($items['group'] as $name) {
                try {
                    $item = $manager->getGroup($name, $this->is_sys);

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
                    print_r($exc->getMessage());
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

        if (!empty($items['role'])) {
            foreach ($items['role'] as $name) {
                try {
                    $item = $manager->getGroup($name, $this->is_sys);
                    $item = $item ?: $manager->getGroupPermission($name);
                    $manager->revokeGroup($item, $this->id);
                    ++$success;
                } catch (\Exception $exc) {
                    throw new ErrorException($exc->getMessage(),400,1,$exc->getFile(),$exc->getLine());
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }
        
        if (isset($items['group'])) {
            foreach ($items['group'] as $name) {
                try {
                    $item = $manager->getGroup($name, $this->is_sys);
                    $item = $item ?: $manager->getGroupPermission($name);
                    $manager->revokeGroup($item, $this->id);
                    ++$success;
                } catch (\Exception $exc) {
                    throw new ErrorException($exc->getMessage(),400,1,$exc->getFile(),$exc->getLine());
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

                    throw new ErrorException($exc->getMessage(),400,1,$exc->getFile(),$exc->getLine());
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
    public function getItems($is_sys = 0,$bloc_id = 0)
    {
        $manager = Configs::authManager();
        $available = [];    
        $all = [];    
        // 用户组授权
        foreach ($manager->getGroups($is_sys,$bloc_id) as $item) {
            $name = $item->name;
            $available['role'][$item->item_id] = $item;
        }
        
        foreach ($manager->getPermissions($is_sys) as $item) {
            $name = $item->name;
            if ($name != '/') {
                // 后续根据情况做优化
                $available['permission'][$item->item_id] = $item;
            }
        }
        
        // 路由授权
        foreach ($manager->getRoutes($is_sys) as $item) {
            $name = $item->name;
            $available['route'][$item->item_id] = $item;
        }

        // $group = AuthAssignmentGroup::find()->where(['user_id' => $this->id])->select(['*','item_name as name'])->asArray()->all();
        
         $assigned = [];
        // foreach ($group as $key => $item) {
        //     $assigned['role'][$item->group_id] = $item;
        //     unset($available['role'][$item->group_id]);
        // }
        

        $assignmentsType = $manager->auth_type;
        $all = $available;
        foreach ($manager->getAssignments($this->id) as $item) {
            // $assigned[$item->roleName] 
            $key  = $assignmentsType[$item->parent_type];
            // $id   = $key==='role'?$item->group_id:$item->item_id;
            $id   = $item->item_id;
            $assigned[$key][$id] = $item;
            unset($available[$key][$item->item_id]);
        }
        
        return [
            'available' => $available,
            'assigned' => $assigned,
            'all' => $all
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
