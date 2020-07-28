<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-07-29 01:42:50
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-07-29 01:42:52
 */
 

namespace diandi\admin\models;

use Yii;

/**
 * This is the model class for table "dd_auth_rule".
 *
 * @property string $name
 * @property resource|null $data
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property AuthItemCopy1[] $authItemCopy1s
 * @property AuthItemCopy2[] $authItemCopy2s
 * @property AuthItem[] $authItems
 * @property AuthRouteCopy1[] $authRouteCopy1s
 * @property AuthRoute[] $authRoutes
 */
class AuthRule extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%auth_rule}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['data'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'data' => 'Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[AuthItemCopy1s]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemCopy1s()
    {
        return $this->hasMany(AuthItemCopy1::className(), ['rule_name' => 'name']);
    }

    /**
     * Gets query for [[AuthItemCopy2s]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemCopy2s()
    {
        return $this->hasMany(AuthItemCopy2::className(), ['rule_name' => 'name']);
    }

    /**
     * Gets query for [[AuthItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItems()
    {
        return $this->hasMany(AuthItem::className(), ['rule_name' => 'name']);
    }

    /**
     * Gets query for [[AuthRouteCopy1s]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthRouteCopy1s()
    {
        return $this->hasMany(AuthRouteCopy1::className(), ['rule_name' => 'name']);
    }

    /**
     * Gets query for [[AuthRoutes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthRoutes()
    {
        return $this->hasMany(AuthRoute::className(), ['title' => 'name']);
    }
}
