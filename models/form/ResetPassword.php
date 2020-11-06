<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-11-06 17:18:40
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-11-06 23:27:29
 */
 

namespace diandi\admin\models\form;

use diandi\admin\components\UserStatus;
use diandi\admin\models\User;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Password reset form
 */
class ResetPassword extends Model
{
    public $password;
    public $retypePassword;
    /**
     * @var User
     */
    private $_user;

    /**
     * Creates a form model given a token.
     *
     * @param  string $token
     * @param  array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidParamException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidParamException('Password reset token cannot be blank.');
        }
        // check token
        $class = Yii::$app->getUser()->identityClass ?: 'diandi\admin\models\User';
        if (static::isPasswordResetTokenValid($token)) {
            $this->_user = $class::findOne([
                'password_reset_token' => $token,
                'status' => UserStatus::ACTIVE
            ]);
        }
        if (!$this->_user) {
            throw new InvalidParamException('Wrong password reset token.');
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'retypePassword'], 'required'],
            ['password', 'string', 'min' => 6],
            ['retypePassword', 'compare', 'compareAttribute' => 'password']
        ];
    }

    /**
     * Resets password.
     *
     * @return boolean if password was reset.
     */
    public function resetPassword()
    {
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();

        return $user->save(false);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = ArrayHelper::getValue(Yii::$app->params, 'user.passwordResetTokenExpire', 24 * 3600);
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

     /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'password'=>'密码',
            'retypePassword'=>'确认密码'
        ];
    }
}
