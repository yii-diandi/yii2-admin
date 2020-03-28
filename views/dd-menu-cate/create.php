<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\DdMenuCate */

$this->title = '添加 顶部导航';
$this->params['breadcrumbs'][] = ['label' => '顶部导航', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<ul class="nav nav-tabs">
    <li  class="active">
        <?= Html::a('添加 顶部导航', ['create'], ['class' => 'btn btn-primary']) ?>
    </li>
    <li>
        <?= Html::a('顶部导航管理', ['index'], ['class' => '']) ?>
    </li>
</ul>
<div class="firetech-main"  style="margin-top:20px;">
    <div class="panel panel-default">
        <div class="box-body">
            <div class="dd-menu-cate-create">

                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>

            </div>
        </div>
    </div>
</div>