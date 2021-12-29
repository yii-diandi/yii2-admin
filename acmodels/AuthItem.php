<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2021-05-21 00:25:03
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-05-21 01:31:11
 */
 

namespace diandi\admin\acmodels;

use Yii;

/**
 * This is the model class for table "{{%auth_item}}".
 *
 * @property int $id
 * @property string $name
 * @property int $type
 * @property string|null $description
 * @property int|null $rule_name
 * @property int|null $parent_id
 * @property resource|null $data
 * @property string|null $module_name
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class AuthItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%auth_item}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'rule_name', 'parent_id', 'created_at', 'updated_at'], 'integer'],
            [['description', 'data'], 'string'],
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
        return $this->hasMany(AuthItemChild::className(),['parent_id'=>'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'type' => 'Type',
            'description' => 'Description',
            'rule_name' => 'Rule Name',
            'parent_id' => 'Parent ID',
            'data' => 'Data',
            'module_name' => 'Module Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
