<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-04-12 13:49:05
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-10-28 19:45:02
 */

namespace diandi\admin\models;

use admin\models\DdApiAccessToken;
use common\models\ActionLog;
use common\models\UserStore;
use diandi\addons\models\AddonsUser;
use diandi\addons\models\BlocStore;
use diandi\addons\models\StoreLabelLink;
use diandi\addons\models\UserBloc;
use diandi\admin\components\Configs;
use diandi\admin\components\Helper;
use diandi\admin\components\UserStatus;
use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model.
 *
 * @property int         $id
 * @property string      $username
 * @property string      $password_hash
 * @property string      $password_reset_token
 * @property string      $email
 * @property string      $auth_key
 * @property int         $status
 * @property int         $created_at
 * @property int         $updated_at
 * @property string      $password             write-only password
 * @property UserProfile $profile
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;

    public $type;

    public $user_id;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return Configs::instance()->userTable;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => \common\behaviors\SaveBehavior::className(),
                'createdAttribute' => 'created_at',
                'updatedAttribute' => 'updated_at',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'in', 'range' => [UserStatus::ACTIVE, UserStatus::INACTIVE]],
            [
                [
                    'username', 'email',
                    'verification_token',
                    'avatar',
                    'mobile',
                    'company',
                ], 'string',
            ],
            [[
                'store_id',
                'bloc_id',
                'is_super_admin',
                'is_business_admin',
                'parent_bloc_id',
                'created_at',
                'updated_at',
            ], 'number'],
        ];
    }

    /**
     * 用户删除处理关联数据
     * @return void
     */
    public function beforeDelete()
    {
        $where['user_id'] = $user_id;
        AuthAssignmentGroup::deleteAll($where);
        AddonsUser::deleteAll($where);
        DdApiAccessToken::deleteAll($where);
        UserBloc::deleteAll($where);
        UserStore::deleteAll($where);
        ActionLog::deleteAll($where);
        //多个用户会共同管理一个商户所以不需要操作删除
//        BlocStore::deleteAll(['store_id' => $this->store_id]);
//        StoreLabelLink::deleteAll(['store_id' => $this->store_id]);
        parent::beforeDelete();
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => UserStatus::ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username.
     *
     * @param string $username
     *
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => UserStatus::ACTIVE]);
    }

    /**
     * Finds user by password reset token.
     *
     * @param string $token password reset token
     *
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => UserStatus::ACTIVE,
        ]);
    }

    public function addChildren($items)
    {
        $AddonsUser = new AddonsUser();
        $success = 0;
        $user_id = $this->id;
        $type = $this->type;
        foreach ($items as $key => $value) {
            $_AddonsUser = clone $AddonsUser;
            $_AddonsUser->setAttributes([
                'type' => $value == 'sys' ? 0 : 1,
                'module_name' => $value,
                'user_id' => $user_id,
                'status' => 1,
            ]);
            $success += $_AddonsUser->save();
        }

        if ($success > 0) {
            Helper::invalidate();
        }

        return $success;
    }

    public function removeChildren($items)
    {
        $AddonsUser = new AddonsUser();
        $success = 0;
        $user_id = $this->id;
        $type = $this->type;

        $success += $AddonsUser->deleteAll(['module_name' => $items]);

        if ($success > 0) {
            Helper::invalidate();
        }

        return $success;
    }

    public function getAddonsUser()
    {
        return $this->hasMany(AddonsUser::className(), ['user_id' => 'id']);
    }

    public function getUserGroup()
    {
        return $this->hasMany(AuthAssignmentGroup::className(), ['user_id' => 'id']);
    }

    /**
     * Finds out if password reset token is valid.
     *
     * @param string $token password reset token
     *
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);

        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password.
     *
     * @param string $password password to validate
     *
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model.
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key.
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token.
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString().'_'.time();
    }

    /**
     * Removes password reset token.
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public static function getDb()
    {
        return Configs::userDb();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'avatar' => '头像',
            'username' => '用户名',
            'email' => '邮箱',
            'status' => '用户状态',
            'id' => '用户ID',
            'created_at' => '注册时间',
        ];
    }
}
