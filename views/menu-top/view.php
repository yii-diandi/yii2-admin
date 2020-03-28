<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\MenuTop */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Menu Tops', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<ul class="nav nav-tabs">
    <li>
        <?= Html::a('添加 Menu Top', ['create'], ['class' => '']) ?>
    </li>
    <li>
        <?= Html::a('Menu Top管理', ['index'], ['class' => '']) ?>
    </li>
    <li class="active">
        <?= Html::a('Menu Top管理', ['view'], ['class' => '']) ?>
    </li>
</ul>
<div class=" firetech-main">
    <div class="menu-top-view">

        <div class="panel panel-default">
            <div class="box-body">

                <p>
                    <?= Html::a('更新', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('删除', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                    ],
                    ]) ?>
                </p>

                <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                            'id',
            'name',
            'mark',
            'sort',
            'create_time',
            'update_time',
            'icon',
                ],
                ]) ?>

            </div>
        </div>
    </div>
</div>