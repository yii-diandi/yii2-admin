<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-04 15:12:29
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-09 05:52:24
 */
 

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\UserGroup */

$this->title = '用户组更新: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'User Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<?= $this->render('_tab') ?>


<div class="firetech-main">
    <div class="panel panel-default">
        <div class="box-body">
            <div class="user-group-update">


                <?= $this->render('_form', [
                'module_name' => $module_name,
                'model' => $model,
                ]) ?>
            </div>
        </div>
    </div>
</div>