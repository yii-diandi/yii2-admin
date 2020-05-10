<?php
/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-28 14:54:57
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-10 16:29:21
 */
 

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\MenuTop */
/* @var $form yii\widgets\MyActiveForm */
?>

<div class="menu-top-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mark')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sort')->textInput() ?>
    
    <?= $form->field($model, 'icon')->widget('diandi\adminlte\widgets\icon', ['options' => [
                'label' => '选择图标',
            ]]); ?>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
