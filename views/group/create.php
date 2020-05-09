<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-04 15:12:39
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-09 05:52:17
 */
 

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\UserGroup */

$this->title = '添加用户组';
$this->params['breadcrumbs'][] = ['label' => 'User Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_tab') ?>

<div class="firetech-main">
    <div class="panel panel-default">
        <div class="box-body">
            <div class="user-group-create">

                <?= $this->render('_form', [
                'model' => $model,
                'module_name' => $module_name,
                ]) ?>

            </div>
        </div>
    </div>
</div>