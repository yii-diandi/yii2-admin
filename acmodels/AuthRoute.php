<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2021-05-21 00:39:56
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-01-13 13:26:18
 */
 

namespace diandi\admin\acmodels;

use Yii;

/**
 * This is the model class for table "{{%auth_route}}".
 *
 * @property int $id
 * @property string $name
 * @property int $type
 * @property int $route_type
 * @property string|null $description
 * @property string|null $title
 * @property int|null $pid
 * @property resource|null $data
 * @property string|null $module_name
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class AuthRoute extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%auth_route}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'is_sys'], 'required'],
            [['is_sys', 'route_type', 'pid', 'created_at', 'updated_at','item_id'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'title','route_name'], 'string', 'max' => 64],
            [['module_name'], 'string', 'max' => 50],
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
                'class' => \common\behaviors\SaveBehavior::className(),
                'updatedAttribute' => 'update_time',
                'createdAttribute' => 'create_time',
            ],
        ];
    }
    
    
    public function getChilds()
    {
        return $this->hasMany(AuthItemChild::className(),['item_id'=>'id']);
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'is_sys' => 'is_sys',
            'item_id' => 'Item_id',
            'route_type' => 'Route Type',
            'description' => 'Description',
            'title' => 'Title',
            'pid' => 'Pid',
            'data' => 'Data',
            'module_name' => 'Module Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
