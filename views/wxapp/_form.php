<?php

use yii\helpers\Html;
use common\widgets\MyActiveForm;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\BlocConfWxapp */
/* @var $form yii\widgets\MyActiveForm */
?>

<div class="bloc-conf-wxapp-form">

    <?php $form = MyActiveForm::begin(); ?>

    <?= $form->field($model, 'bloc_id')->textInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textInput() ?>

    <?= $form->field($model, 'original')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'AppId')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'headimg')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'AppSecret')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'create_time')->textInput() ?>

    <?= $form->field($model, 'update_time')->textInput() ?>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php MyActiveForm::end(); ?>

</div>
