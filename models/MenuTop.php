<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-28 11:39:36
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-07-02 19:03:19
 */


namespace diandi\admin\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "dd_menu_cate".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $mark
 * @property int|null $sort
 * @property string|null $create_time
 * @property string|null $update_time
 * @property string|null $icon
 */
class MenuTop extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%auth_menu_cate}}';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sort'], 'integer'],
            [['name', 'mark'], 'string', 'max' => 255],
            // [['create_time', 'update_time', 'icon'], 'string', 'max' => 30],
        ];
    }

    // public function behaviors()
    // {
    //     return [
    //         'class' => TimestampBehavior::className(),
    //         'createdAtAttribute' => 'create_time',
    //         'updatedAtAttribute' => 'update_time'

    //     ];
    // }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'mark' => '备注',
            'sort' => '排序',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'icon' => '图标',
        ];
    }
}
