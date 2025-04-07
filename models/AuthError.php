<?php

namespace diandi\admin\models;

use common\traits\ActiveQuery\StoreTrait;

/**
 * This is the model class for table "{{%auth_error}}".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $itemName
 * @property string|null $params
 * @property string|null $assignments
 * @property string|null $parent_type
 * @property string|null $create_time
 * @property string|null $update_time
 * @property int|null $bloc_id
 * @property int|null $store_id
 */
class AuthError extends \yii\db\ActiveRecord
{
    use StoreTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%auth_error}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'bloc_id', 'store_id'], 'integer'],
            [['create_time', 'update_time', 'assignments'], 'safe'],
            [['itemName', 'params', 'parent_type'], 'safe'],
        ];
    }

    /**
     * 行为.
     */
    public function behaviors(): array
    {
        /*自动添加创建和修改时间*/
        return [
            [
                'class' => \common\behaviors\SaveBehavior::className(),
                'updatedAttribute' => 'update_time',
                'createdAttribute' => 'create_time',
                'time_type' => 'datetime',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'itemName' => 'Item Name',
            'params' => 'Params',
            'assignments' => 'Assignments',
            'parent_type' => 'Parent Type',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'bloc_id' => 'Bloc ID',
            'store_id' => 'Store ID',
        ];
    }
}