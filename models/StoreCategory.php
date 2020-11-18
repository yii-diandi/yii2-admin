<?php

namespace diandi\admin\models;

use Yii;

/**
 * This is the model class for table "{{%diandi_store_category}}".
 *
 * @property int $category_id 分类id
 * @property string $name 分类名称
 * @property int $parent_id 父级id
 * @property string $thumb 分类图片
 * @property int $sort 分类排序
 * @property int $create_time
 * @property int $update_time
 */
class StoreCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%diandi_store_category}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id', 'sort', 'create_time', 'update_time'], 'integer'],
            [['thumb'], 'required'],
            [['name'], 'string', 'max' => 50],
            [['thumb'], 'string', 'max' => 250],
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
            'category_id' => '分类id',
            'name' => '分类名称',
            'parent_id' => '父级id',
            'thumb' => '分类图片',
            'sort' => '分类排序',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
