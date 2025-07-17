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

    static function getUserIsSys($user_id): int
    {
        $is_sys = User::find()->andWhere(['id' => $user_id])->select('is_sys')->scalar();
        return  $is_sys ?? 1;
    }

    static function userInfo($userId, $cloumns = ['*']): array
    {
        $user = User::find()->andWhere(['id' => $userId])->select($cloumns)->asArray()->one();
        return $user ?? [];
    }
}
