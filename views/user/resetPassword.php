<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \diandi\admin\models\form\ResetPassword */

$this->title = 'Reset password';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-reset-password">
    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
                <?= $form->field($ResetPassword, 'password')->passwordInput() ?>
                <?= $form->field($ResetPassword, 'retypePassword')->passwordInput() ?>
                <div class="form-group">
                    <?= Html::submitButton(Yii::t('rbac-admin', 'Save'), ['class' => 'btn btn-primary']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
