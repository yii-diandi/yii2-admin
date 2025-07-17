<?php

/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2025-07-17 21:00:50
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2025-07-17 21:14:16
 */

namespace diandi\admin\components;

use diandi\admin\models\User;
use Yii;

class AuthUserServer
{
    function __construct()
    {
        if (!in_array(Yii::$app->id, ['app-console'])) {
            $access_token = Yii::$app->request->headers->get('access-token', 0);
            if (empty($access_token)) {
                $access_token = Yii::$app->request->input('access-token', '');
            }
            $arr = explode('_', $access_token);
            $userType = $arr[0] ?? 'admin';
            if ($userType == 'admin') {
                Yii::$app->setComponents([
                    'user' => [
                        'class' => 'yii\web\User',
                        'identityClass' => 'admin\models\DdApiAccessToken',
                        'enableAutoLogin' => true,
                        'enableSession' => true,
                        'loginUrl' => null,
                        'identityCookie' => ['name' => '_identity-admin', 'httpOnly' => true]
                    ],
                ]);
            } elseif ($userType == 'customer') {
                Yii::$app->setComponents([
                    'user' => [
                        'class' => 'yii\web\User',
                        'identityClass' => 'admin\modules\customer\models\DdApiAccessToken',
                        'enableAutoLogin' => true,
                        'enableSession' => true,
                        'loginUrl' => null,
                        'identityCookie' => ['name' => '_identity-customer-api', 'httpOnly' => true]
                    ]
                ]);
            }
        }
    }

    function getUserIsSys($user_id): int
    {
        $is_sys = User::find()->andWhere(['id' => $user_id])->select('is_sys')->scalar();
        return  $is_sys ?? 1;
    }

    function userInfo($userId, $cloumns = ['*']): array
    {
        $user = User::find()->andWhere(['id' => $userId])->select($cloumns)->asArray()->one();
        return $user ?? [];
    }
}
