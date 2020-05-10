<?php
/**
 * @Author: Wang chunsheng
 * @Date:   2020-04-29 02:32:12
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-10 17:00:23
 */
use common\widgets\tab\Tab;

?>
<?= Tab::widget([
    'titles' => ['菜单管理', '新增菜单', '菜单维护','菜单编辑'],
    'options'=>[
        'module_name'=> Yii::$app->request->get('module_name','sys')
    ]
    ]); ?>


