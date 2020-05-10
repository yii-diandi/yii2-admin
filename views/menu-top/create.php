<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-04-13 23:44:55
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-10 16:24:31
 */


use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\MenuTop */

$this->title = '添加顶部导航';
$this->params['breadcrumbs'][] = ['label' => '顶部导航', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_tab', [
                    'model' => $model,
                ]) ?>
<div class="firetech-main">
    <div class="panel panel-default">
        <div class="box-body">
            <div class="menu-top-create">

                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>

            </div>
        </div>
    </div>
</div>