<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-04-14 10:21:32
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-19 06:59:27
 */


use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model diandi/admin\models\User */
/* @var $form yii\widgets\MyActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>
   <?= $form->field($model, 'avatar')->widget('common\widgets\webuploader\FileInput', []); ?>
    <?= $form->field($model, 'username')->textInput() ?>
    <?= $form->field($model, 'email')->textInput() ?>
    <?= $form->field($model, 'status')->radioList(['0' => '待审核', '10' => '审核通过'])->label('审核状态') ?>
    
    


    <?= Html::submitButton('保存', ['class' => 'btn btn-primary']) ?>


    <?php ActiveForm::end(); ?>

</div>