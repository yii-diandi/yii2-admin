<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-03 15:46:52
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-10 23:55:35
 */

namespace diandi\admin\models;

use diandi\admin\components\Configs;
use diandi\admin\components\Helper;
use Yii;
use yii\base\Model;
use yii\helpers\Json;
use diandi\admin\components\Item;
use diandi\admin\components\Route;

class AuthItem extends Model
{
    public $name;
    public $type;
    public $description;
    public $ruleName;
    public $data;
    public $parent_id;
    public $module_name;
    public $child_type;
    // 0:路由1权限2用户组
    public $parent_type;
    public $parent_name;
    
    /**
     * @var Item
     */
    private $_item;

    /**
     * Initialize object.
     *
     * @param Item  $item
     * @param array $config
     */
    public function __construct($item = null, $config = [])
    {
        $this->_item = $item;

        if ($item !== null) {
            $this->name = $item->name;
            $this->parent_id = $item->parent_id;
            $this->module_name = $item->module_name;
            $this->type = $item->type;
            $this->child_type = $item->child_type;
            $this->parent_type = $item->parent_type;
            $this->description = $item->description;
            $this->ruleName = $item->ruleName;
            $this->data = $item->data === null ? null : Json::encode($item->data);
        }
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ruleName'], 'checkRule'],
            [['name', 'type'], 'required'],
            [['name'], 'checkUnique', 'when' => function () {
                return $this->isNewRecord || ($this->_item->name != $this->name);
            }],
            [['type', 'child_type'], 'integer'],
            [['parent_id'], 'checkParent'],
            [['description', 'data', 'ruleName'], 'default'],
            [['name', 'parent_id', 'module_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * Check role is unique.
     */
    public function checkUnique()
    {
        $authManager = Configs::authManager();
        $value = $this->name;
        if ($authManager->getRole($value) !== null || $authManager->getPermission($value) !== null) {
            $message = Yii::t('yii', '{attribute} "{value}" has already been taken.');
            $params = [
                'attribute' => $this->getAttributeLabel('name'),
                'value' => $value,
            ];
            $this->addError('name', Yii::$app->getI18n()->format($message, $params, Yii::$app->language));
        }
    }


    /**
     * Check for rule.
     */
    public function checkRule()
    {
        $name = $this->ruleName;
        if (!Configs::authManager()->getRule($name)) {
            try {
                $rule = Yii::createObject($name);
                if ($rule instanceof \yii\rbac\Rule) {
                    $rule->name = $name;
                    Configs::authManager()->add($rule);
                } else {
                    $this->addError('ruleName', Yii::t('rbac-admin', 'Invalid rule "{value}"', ['value' => $name]));
                }
            } catch (\Exception $exc) {
                $this->addError('ruleName', Yii::t('rbac-admin', 'Rule "{value}" does not exists', ['value' => $name]));
            }
        }
    }

    public function checkParent()
    {
        $parent_id = $this->parent_id;
        if($parent_id){
            $manager = Configs::authManager();
            $item = $manager->getPermission($parent_id);
            if (!$item) {
                $this->addError('parent_id', Yii::t('rbac-admin', '父级权限 "{value}" 不存在', ['value' => $parent_id]));
            } 
        }
       
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('rbac-admin', 'Name'),
            'parent_id' => Yii::t('rbac-admin', 'parent_id'),
            'parent_name'=> Yii::t('rbac-admin', 'parent_id'),
            'module_name' => Yii::t('rbac-admin', 'module_name'),
            'type' => Yii::t('rbac-admin', 'Type'),
            'description' => Yii::t('rbac-admin', 'Description'),
            'ruleName' => Yii::t('rbac-admin', 'Rule Name'),
            'data' => Yii::t('rbac-admin', 'Data'),
        ];
    }

    /**
     * Check if is new record.
     *
     * @return bool
     */
    public function getIsNewRecord()
    {
        return $this->_item === null;
    }

    /**
     * Find role.
     *
     * @param string $id
     *
     * @return \self|null
     */
    public static function find($id)
    {
        $item = Configs::authManager()->getRole($id);
        if ($item !== null) {
            return new self($item);
        }

        return null;
    }

    /**
     * Save role to [[\yii\rbac\authManager]].
     *
     * @return bool
     */
    public function save()
    {
        if ($this->validate()) {
            $manager = Configs::authManager();
            if ($this->_item === null) {
                // if ($this->type == Item::TYPE_PERMISSION) {
                //     $this->_item = $manager->createRole($this->name);
                // } else {
                //     $this->_item = $manager->createPermission($this->name);
                // }
                $this->_item = $manager->createPermission($this->name);

                $isNew = true;
            } else {
                $isNew = false;
                $oldName = $this->_item->name;
            }

            $this->_item->name = $this->name;
            $this->_item->type = $this->type;
            $this->_item->module_name = $this->module_name;
            $this->_item->parent_id = $this->parent_id?$this->parent_id:0;
            $this->_item->description = $this->description;
            $this->_item->ruleName = $this->ruleName;
            $this->_item->data = $this->data === null || $this->data === '' ? null : Json::decode($this->data);
            if ($isNew) {
                $manager->add($this->_item);
            } else {
                $manager->update($oldName, $this->_item);
            }
            Helper::invalidate();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Adds an item as a child of another item.
     *
     * @param array $items
     *
     * @return int
     */
    public function addChildren($items,$parent_type=1)
    {
        $manager = Configs::authManager();
        $success = 0;
        if ($this->_item) {
            if ($items['route']) {
                foreach ($items['route'] as $name) {
                    $child = $manager->getRoutePermission($name,$this->parent_type);
                    try {
                        $manager->addChild($this->_item, $child);
                        ++$success;
                    } catch (\Exception $exc) {
                        p($exc->getMessage());
                        Yii::error($exc->getMessage(), __METHOD__);
                    }
                }
            }

            if ($items['permission']) {
                foreach ($items['permission'] as $name) {
                    $child = $manager->getPermission($name);
                    $child->parent_type = $parent_type;
                    try {
                        $manager->addChild($this->_item, $child);
                        ++$success;
                    } catch (\Exception $exc) {
                        Yii::error($exc->getMessage(), __METHOD__);
                    }
                }
            }
            // foreach ($items as $name) {
            //     $child = $manager->getPermission($name);

            //     try {
            //         $manager->addChild($this->_item, $child);
            //         ++$success;
            //     } catch (\Exception $exc) {
            //         print_r($exc->getMessage());
            //         Yii::error($exc->getMessage(), __METHOD__);
            //     }
            // }
        }
        if ($success > 0) {
            Helper::invalidate();
        }

        return $success;
    }

    /**
     * Remove an item as a child of another item.
     *
     * @param array $items
     *
     * @return int
     */
    public function removeChildren($items)
    {
        $manager = Configs::authManager();
        $success = 0;
        if ($this->_item !== null) {
            if ($items['route']) {
                foreach ($items['route'] as $name) {
                    $child = $manager->getRoutePermission($name);

                    try {
                        $manager->removeChild($this->_item, $child);
                        ++$success;
                    } catch (\Exception $exc) {
                        Yii::error($exc->getMessage(), __METHOD__);
                    }
                }
            }

            if ($items['permission']) {
                foreach ($items['permission'] as $name) {
                    $child = $manager->getPermission($name);
                    try {
                        $manager->removeChild($this->_item, $child);
                        ++$success;
                    } catch (\Exception $exc) {
                        Yii::error($exc->getMessage(), __METHOD__);
                    }
                }
            }
        }
        if ($success > 0) {
            Helper::invalidate();
        }

        return $success;
    }

    /**
     * Get items.
     *
     * @return array
     */
    public function getItems()
    {
        $manager = Configs::authManager();
        $available = [];
        if ($this->type == Item::TYPE_PERMISSION) {
            foreach (array_keys($manager->getRoles()) as $name) {
                $available[$name] = 'role';
            }
        }
        foreach (array_keys($manager->getPermissions()) as $name) {
            $available[$name] = $name[0] == '/' ? 'route' : 'permission';
        }
        // 路由授权
        foreach (array_keys($manager->getRoutes(Route::TYPE_ROLE)) as $name) {
            $available[$name] = 'route';
        }
        $assigned = [];

        foreach ($manager->getChildren($this->_item->name) as $item) {
            $assigned[$item->name] = $item->type == 1 ? 'role' : ($item->name[0] == '/' ? 'route' : 'permission');
            unset($available[$item->name]);
        }

        foreach ($manager->getItemChildren($this->_item->name) as $item) {
            $child_type = ['route', 'permission', 'role'];
            $assigned[$item->name] = $child_type[$item->child_type];
            unset($available[$item->name]);
        }
        unset($available[$this->name]);

        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }

    /**
     * Get item.
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->_item;
    }

    /**
     * Get type name.
     *
     * @param mixed $type
     *
     * @return string|array
     */
    public static function getTypeName($type = null)
    {
        $result = [
            Item::TYPE_PERMISSION => 'Permission',
        ];
        if ($type === null) {
            return $result;
        }

        return $result[$type];
    }
}
