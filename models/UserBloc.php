<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-01 19:12:40
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-01 19:13:05
 */

namespace diandi\admin\models;

/**
 * This is the model class for table "diandi_user_bloc".
 *
 * @property int         $id
 * @property int|null    $user_id     管理员id
 * @property int|null    $bloc_id     集团id
 * @property int|null    $store_id    子公司id
 * @property string|null $create_time
 * @property string|null $update_time
 */
class UserBloc extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'diandi_user_bloc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'bloc_id', 'store_id', 'status'], 'integer'],
            [['create_time', 'update_time'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => '审核状态',
            'user_id' => '管理员id',
            'bloc_id' => '集团id',
            'store_id' => '子公司id',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
