<?php
/**
 * @Author: Wang chunsheng
 * @Date:   2020-04-29 02:32:12
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-04 10:56:53
 */
use common\widgets\tab\Tab;

?>
<?= Tab::widget([
    'titles' => ['权限管理', '新增权限', '权限维护','权限编辑'],
    'options'=>[
        'module_name'=> Yii::$app->request->get('module_name','sys')
    ]
    ]); ?>


