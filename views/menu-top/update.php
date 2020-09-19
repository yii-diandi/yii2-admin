<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-10 16:24:46
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-09-19 10:13:55
 */

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\MenuTop */

$this->title = 'Update顶部导航: '.$model->name;
$this->params['breadcrumbs'][] = ['label' => '顶部导航s', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<?= $this->render('_tab', [
                ]); ?>
<div class="firetech-main">
    <div class="panel panel-default">
        <div class="box-body">
            <div class="menu-top-update">


                <?= $this->render('_form', [
                'model' => $model,
                ]); ?>
            </div>
        </div>
    </div>
</div>