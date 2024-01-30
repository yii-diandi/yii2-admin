<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2021-05-21 00:25:27
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-01-16 02:57:37
 */
 

namespace diandi\admin\acmodels;

use diandi\admin\components\Configs;
use diandi\admin\components\DbManager;
use Yii;

/**
 * This is the model class for table "{{%auth_item_child}}".
 *
 * @property int $id
 * @property int|null $type
 * @property int|null $item_id
 * @property int $parent_id
 * @property string $parent
 * @property string $child
 * @property string|null $module_name
 * @property int|null $child_type 0:route,1:permission,2:role
 * @property int|null $parent_type 0:路由1：规则2：用户组
 */
class AuthItemChild extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    { 
        $manager = Configs::authManager();
        return $manager->itemChildTable;// '{{%auth_item_child}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_sys', 'item_id', 'parent_item_id', 'parent_id', 'child_type', 'parent_type'], 'integer'],
            [['parent_id', 'parent', 'child'], 'required'],
            [['parent', 'child'], 'string', 'max' => 64],
            [['module_name'], 'string', 'max' => 50],
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
                'class' => \common\behaviors\SaveBehavior::className(),
                'updatedAttribute' => 'update_time',
                'createdAttribute' => 'create_time',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'is_sys' => 'Is_sys',
            'item_id' => 'Item ID',
            'parent_id' => 'Parent ID',
            'parent' => 'Parent',
            'child' => 'Child',
            'module_name' => 'Module Name',
            'child_type' => '0:route,1:permission,2:role',
            'parent_type' => '0:路由1：规则2：用户组',
        ];
    }
}
