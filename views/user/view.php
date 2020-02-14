<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model diandi/admin\models\User */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<ul class="nav nav-tabs">
    <li>
        <?= Html::a('添加 User', ['create'], ['class' => '']) ?>
    </li>
    <li>
        <?= Html::a('User管理', ['index'], ['class' => '']) ?>
    </li>
    <li  class="active">
        <?= Html::a('User查看', ['view'], ['class' => '']) ?>
    </li>
</ul>
<div class=" firetech-main">
<div class="user-view">

    <div class="panel panel-default">
        <div class="panel-body">

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
            'username',
            'auth_key',
            'password_hash',
            'password_reset_token',
            'email:email',
            'status',
            'created_at',
            'updated_at',
            'verification_token',
            'avatar',
        ],
    ]) ?>

</div>
    </div>
</div>
</div>
