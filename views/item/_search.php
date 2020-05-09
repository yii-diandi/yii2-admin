<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-03 20:15:10
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-08 18:04:50
 */
 

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\searchs\BlocConfEmailSearch */
/* @var $form yii\widgets\ActiveForm */
?>


<div class="panel panel-info">
      <div class="panel-heading">
            <h3 class="panel-title">搜索</h3>
      </div>
      <div class="panel-body">
           
    

<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

    <div class="bloc-conf-email-search">

        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
        ]); ?>

        <div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>

            <?= $form->field($model, 'name') ?>

        </div>

        <div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>

            <?= $form->field($model, 'parent_id') ?>

        </div>

        <div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>

            <?= $form->field($model, 'description') ?>

        </div>

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
