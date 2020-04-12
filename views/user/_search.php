<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-04-12 13:54:26
 * @Last Modified by:   Wang Chunsheng 2192138785@qq.com
 * @Last Modified time: 2020-04-12 14:02:35
 */


use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model diandi/admin\models\searchs\User */
/* @var $form yii\widgets\ActiveForm */
?>


<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">搜索管理员</h3>
    </div>
    <div class="panel-body">
        <div class="user-search">

            <?php $form = ActiveForm::begin([
                'action' => ['index'],
                'method' => 'get',
            ]); ?>

            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                <?= $form->field($model, 'id') ?>
                <?= $form->field($model, 'username') ?>

            </div>


            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                <?php echo $form->field($model, 'status')     ?>
                <?php echo $form->field($model, 'created_at')    ?>
            </div>

            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                <?php echo $form->field($model, 'email')     ?>
            </div>


            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="form-group">
                    <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
                    <?= Html::resetButton('重置', ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>

        </div>

    </div>
</div>