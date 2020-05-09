<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-04 15:21:43
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-09 10:02:45
 */
 

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\UserGroup */
/* @var $form yii\widgets\MyActiveForm */
?>

<div class="user-group-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <?= Html::activeHiddenInput($model,'module_name',array('value'=>$module_name)) ?>
    <?= Html::activeHiddenInput($model,'type',array('value'=>$module_name=='sys'?0:1)) ?>
    <?= $form->field($model, 'name')->hint('建议：模块名称-用户组名称')->textInput(['maxlength' => true]) ?>


    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

 

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
