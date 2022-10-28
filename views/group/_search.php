<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2022-10-26 14:18:07
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-10-28 14:40:46
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\searchs\UserGroupSearch */
/* @var $form yii\widgets\ActiveForm */
?>


<div class="panel panel-info">
      <div class="panel-heading">
            <h3 class="panel-title">搜索</h3>
      </div>
      <div class="panel-body">
           
    

<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    


<div class="user-group-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>

    <?= $form->field($model, 'id'); ?>

</div>

<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>

    <?= $form->field($model, 'name'); ?>

</div>

<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>

    <?= $form->field($model, 'type'); ?>

</div>

<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>

    <?= $form->field($model, 'description'); ?>

</div>

<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>

    <?= $form->field($model, 'created_at'); ?>

</div>

<div class='col-xs-4 col-sm-4 col-md-4 col-lg-4'>

    <?php // echo $form->field($model, 'updated_at')?>

</div>


</div>


<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
<div class="form-group">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']); ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-outline-secondary']); ?>
    </div>

</div>

  
    <?php ActiveForm::end(); ?>

</div>
</div>
</div>
