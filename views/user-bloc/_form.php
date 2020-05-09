<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-01 19:13:36
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-03 06:42:37
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\UserBloc */
/* @var $form yii\widgets\MyActiveForm */

?>

<div class="user-bloc-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= Html::activeHiddenInput($model, 'user_id', ['id' => 'user_id']); ?>

    <?= $form->field($model, 'user_id')->textInput(['id' => 'user_name']); ?>

    <?= $form->field($model, 'bloc_id')->textInput(); ?>

    <?= $form->field($model, 'store_id')->textInput(); ?>

    <?php echo $form->field($model, 'status')->radioList([
        '1' => '审核通过',
        '0' => '待审核',
    ]); ?>


    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']); ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
