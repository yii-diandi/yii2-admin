<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\BlocConfBaidu */

$this->title = '添加 Bloc Conf Baidu';
$this->params['breadcrumbs'][] = ['label' => 'Bloc Conf Baidus', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<ul id="w1" class="nav nav-tabs"><li><a href="/backend/gii">管理</a></li>
<li><a href="/backend/gii/default/create">添加</a></li>
<li class="active"><a href="/backend/gii/default/view">详情</a></li></ul>
<div class="tab-content"><div id="w1-tab0" class="tab-pane"></div>
<div id="w1-tab1" class="tab-pane"></div>
<div id="w1-tab2" class="tab-pane active"></div></div>




<div class="firetech-main">
    <div class="panel panel-default">
        <div class="box-body">
            <div class="bloc-conf-baidu-create">

                <?= $this->render('_form', [
                'model' => $model,
                ]) ?>

            </div>
        </div>
    </div>
</div>