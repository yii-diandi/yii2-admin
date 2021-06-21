<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-04 15:21:33
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-05-24 19:13:38
 */

namespace diandi\admin\models;

use diandi\addons\models\DdAddons;
use diandi\admin\components\Configs;
use diandi\admin\components\Helper;
use diandi\admin\components\Item;
use Yii;

/**
 * This is the model class for table "dd_auth_user_group".
 *
 * @property int         $id
 * @property string      $name        用户组名称
 * @property int         $type        用户组类型
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
     * 行为.
     */
    public function behaviors()
    {
        /*自动添加创建和修改时间*/
        return [
            [
                'class' => \common\behaviors\SaveBehavior::className(),
                'createdAttribute'=>'created_at',
                'updatedAttribute'=>'updated_at',
            ],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['description','module_name'], 'string'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'checkName'],
            [['name'], 'unique'],
            
            
        ];
    }

    public function getAddons()
    {
        return $this->hasOne(DdAddons::className(),['identifie'=>'module_name']);
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
            $this->name = $item->name;
            $this->module_name = $item->module_name;
            $this->type = $item->type;
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
            if ($items) {
                // $group = $items['group'];
                foreach ($items as $name =>$val) {
                    $id = $val['id'];
                    $child = $manager->getGroupPermission($id);
                  
                    try {
                        $res = $manager->addChild($this->_item, $child);
                        ++$success;
                    } catch (\Exception $exc) {
                        p($exc->getMessage());
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
            foreach ($items as $name) {
                $child = $manager->getGroupPermission($name);
                try {
                    $manager->removeChild($this->_item, $child);
                    ++$success;
                } catch (\Exception $exc) {
                    Yii::error($exc->getMessage(), __METHOD__);
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
            'type' => '用户组类型',
            'description' => '用户组说明',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
