<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\MenuTop */

$this->title = 'Update Menu Top: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Menu Tops', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<ul class="nav nav-tabs">
    <li class="active">
        <?= Html::a('添加 Menu Top', ['create'], ['class' => 'btn btn-primary']) ?>
    </li>
    <li>
        <?= Html::a('Menu Top管理', ['index'], ['class' => '']) ?>
    </li>
</ul>
<div class="firetech-main">
    <div class="panel panel-default">
        <div class="box-body">
            <div class="menu-top-update">


                <?= $this->render('_form', [
                'model' => $model,
                ]) ?>
            </div>
        </div>
    </div>
</div>