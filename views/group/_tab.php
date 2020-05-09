<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-04 15:11:58
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-09 05:50:17
 */
 

use common\widgets\tab\Tab;

?>
<?= Tab::widget([
    'titles' => [
            '用户组管理',
            '添加用户组',
            '用户组详情',
            '更新用户组',
    ],
    'options'=>[
        'module_name'=> Yii::$app->request->get('module_name','sys')
    ]
]); ?>


