<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-11-06 21:26:26
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-11-07 00:06:52
 */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $title.'修改密码';
$this->params['breadcrumbs'][] = $this->title;

?>

<?= $this->render('_tab', []) ?>



<div class="firetech-main">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="user-create">
                
                <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
                    <?= $form->field($ResetPassword, 'password')->passwordInput() ?>
                    <?= $form->field($ResetPassword, 'retypePassword')->passwordInput() ?>
                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('rbac-admin', '保存'), ['class' => 'btn btn-primary']) ?>
                        <a href="<?= Url::to(['update','id'=>$id])?>">
                            <?= Html::Button('返回列表', [
                                'class' => 'btn btn-primary',
                                'type'=>'button'
                                ]) ?>
                        </a>
                    </div>
                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>

