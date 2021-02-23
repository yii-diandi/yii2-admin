<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-08 08:52:46
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-02-23 18:41:59
 */
 

namespace diandi\admin\models;

use Yii;

/**
 * This is the model class for table "dd_auth_item".
 *
 * @property string $name
 * @property int $type
 * @property string|null $description
 * @property string|null $rule_name
 * @property string|null $parent_id
 * @property resource|null $data
 * @property string|null $module_name
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthRule $ruleName
 */
class AuthItemModel extends \yii\db\ActiveRecord
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
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'rule_name', 'parent_id'], 'string', 'max' => 64],
            [['module_name'], 'string', 'max' => 50],
            // [['name'], 'unique'],
            [['rule_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthRule::className(), 'targetAttribute' => ['rule_name' => 'name']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => '权限名称',
            'type' => '权限类型',
            'description' => '权限描述',
            'rule_name' => '规则名称',
            'parent_id' => '父级权限',
            'data' => '数据',
            'module_name' => '所属模块',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[AuthAssignments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['item_name' => 'name']);
    }

    /**
     * Gets query for [[RuleName]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRuleName()
    {
        return $this->hasOne(AuthRule::className(), ['name' => 'rule_name']);
    }
}
