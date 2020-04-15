<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-30 21:44:16
 * @Last Modified by:   Wang Chunsheng 2192138785@qq.com
 * @Last Modified time: 2020-04-05 14:40:33
 */


use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\bloc\models\Bloc */

$this->title = 'Update Bloc: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Blocs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<ul class="nav nav-tabs">

    <li>
        <?= Html::a('公司管理', ['index'], ['class' => '']) ?>
    </li>
    <li class="active">
        <?= Html::a('添加公司', ['create'], ['class' => 'btn btn-primary']) ?>
    </li>
</ul>
<div class="firetech-main">
    <div class="panel panel-default">
        <div class="box-body">
            <div class="bloc-update">


                <?= $this->render('_form', [
                    'parents' => $parents,
                    'model' => $model,
                ]) ?>
            </div>
        </div>
    </div>
</div>