<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\searchs\BlocConfBaiduSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bloc-conf-baidu-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'bloc_id') ?>

    <?= $form->field($model, 'APP_ID') ?>

    <?= $form->field($model, 'API_KEY') ?>

    <?= $form->field($model, 'SECRET_KEY') ?>

    <?php // echo $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'update_time') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
