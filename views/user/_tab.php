<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-04 15:11:58
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-11-06 23:55:16
 */
 

use common\widgets\tab\Tab;

?>
<?= Tab::widget([
    'titles' => [
            '管理员管理',
            '添加管理员',
            '管理员详情',
            '更新管理员',
            // '修改密码',
    ],
    'urls'=>[
      'index',
      'signup',
      'view',
      'update',  
      // 'change-pass',  
    ],
    'options'=>[
        'module_name'=> Yii::$app->request->get('module_name','sys')
    ]
]); ?>


