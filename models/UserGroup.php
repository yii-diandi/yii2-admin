<?php

/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-04 15:21:33
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-10-28 18:44:19
 */

namespace diandi\admin\models;

use diandi\admin\components\Configs;
use diandi\admin\components\Helper;
use diandi\admin\components\Item;
use Yii;
use yii\base\InvalidArgumentException;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "dd_auth_user_group".
 *
 * @property int         $id
 * @property string      $name        用户组名称
 * @property string|null $description 用户组名称
 * @property int|null    $created_at
 * @property int|null    $updated_at
 */
class UserGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%auth_user_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'is_sys'], 'required'],
            ['is_sys', 'in', 'range' => [0, 1]],
            [['created_at', 'updated_at', 'store_id','type', 'bloc_id', 'item_id', 'is_sys', 'is_default'], 'integer'],
            [['description'], 'string'],
            [['bloc_id', 'store_id'], 'default', 'value' => 0],
            ['is_sys', 'default', 'value' => 1],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique'],
        ];
    }

    /**
     * 行为.
     */
    public function behaviors()
    {
        /*自动添加创建和修改时间*/
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time(),
            ],
        ];
    }

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
            $this->id = $item->id;
            $this->item_id = $item->item_id;
            $this->is_sys = $item->is_sys;
            $this->name = $item->name;
            $this->description = $item->description;
        }
        parent::__construct($config);
    }

    public function checkName()
    {
        $name = $this->name;
        // 不能和权限名称相同
        $manager = Configs::authManager();
        $item = $manager->getPermission($name);
        if ($item) {
            $this->addError('name', '名称：'.$item->name.'已存在');

            return;
        }
    }

    /**
     * Adds an item as a child of another item.
     *
     * @param array $items
     *
     * @return int
     */
    public function addChildren($items)
    {
        $manager = Configs::authManager();
        $success = 0;

        if ($this->_item) {
            if (!empty($items['group'])) {
                $group = $items['group'];
                foreach ($group as $name => $val) {
                    $id = $val;
                    $child = $manager->getGroupPermission($id);
                    try {
                        $res = $manager->addChild($this->_item, $child);
                        ++$success;
                    } catch (\Exception $exc) {
                        Yii::error($exc->getMessage(), __METHOD__);
                        throw new InvalidArgumentException($exc->getMessage());
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
            if (!empty($items['group'])) {
                foreach ($items['group'] as $name) {
                    $child = $manager->getGroupPermission($name);
                    try {
                        $manager->removeChild($this->_item, $child);
                        ++$success;
                    } catch (\Exception $exc) {
                        Yii::error($exc->getMessage(), __METHOD__);
                        throw new InvalidArgumentException($exc->getMessage());
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
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '用户组名称',
            'is_sys' => '用户组类型',
            'description' => '用户组说明',
            'store_id' => '商户',
            'bloc_id' => '公司',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
