<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model mdm\admin\models\User */

$this->title = '添加 User';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<ul class="nav nav-tabs">
    <li  class="active">
        <?= Html::a('添加 User', ['create','plugins'=>$plugins], ['class' => 'btn btn-primary']) ?>
    </li>
    <li>
        <?= Html::a('User管理', ['index','plugins'=>$plugins], ['class' => '']) ?>
    </li>
</ul>
<div class="firetech-main">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="user-create">

                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>

            </div>
        </div>
    </div>
</div>