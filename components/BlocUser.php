<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-01 19:01:08
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-03 07:30:04
 */

namespace diandi\admin\components;

use diandi\admin\models\UserBloc;
use Yii;

class BlocUser
{
    public static function getMybloc()
    {
        $user_id = Yii::$app->user->identity->id;
        $blocs = UserBloc::findAll(['user_id' => $user_id, 'status' => 1]);

        return $blocs;
    }

    public function getUser()
    {
    }
}
