<?php

namespace diandi\admin\acmodels;

use diandi\admin\models\UserGroup;
use Yii;

class AuthAssignmentGroupMenu extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%auth_assignment_group_menu}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'group_id', 'item_id', 'group_item_id', 'group_name', 'created_at','module_name','is_options'], 'safe'],
        ];
    }

    /**
     * 获取item
     */
    public function getItem()
    {
        return $this->hasOne(AuthItem::className(), ['id' => 'item_id']);
    }

    /**
     * 获取group
     */
    public function getGroup()
    {
        return $this->hasOne(UserGroup::className(), ['item_id' => 'group_item_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'group_id'      => 'group_id',
            'item_id'       => 'item_id',
            'group_name'    => 'group_name',
            'group_item_id' => 'group_item_id',
            'created_at'    => 'created_at',
        ];
    }
}
