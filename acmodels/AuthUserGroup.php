<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2021-05-21 00:35:44
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-01-16 02:58:08
 */
 

namespace diandi\admin\acmodels;

use diandi\admin\components\Configs;
use diandi\admin\components\DbManager;
use Yii;

/**
 * This is the model class for table "{{%auth_user_group}}".
 *
 * @property int $id
 * @property string $name 用户组名称
 * @property int $type 用户组类型
 * @property string|null $description 用户组名称
 * @property string|null $module_name
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class AuthUserGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        $manager = Configs::authManager();
        return $manager->groupTable;// '{{%auth_user_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'created_at', 'updated_at','item_id'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 64],
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


    public function getChilds()
    {
        return $this->hasMany(AuthItemChild::className(),['parent_id'=>'item_id']);
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
            'description' => '用户组名称',
            'module_name' => 'Module Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
