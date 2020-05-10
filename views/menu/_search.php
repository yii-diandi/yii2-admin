<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-10 17:02:10
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-10 17:03:28
 */
 

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var diandi/admin\models\searchs\Menu $model
 * @var yii\widgets\ActiveForm $form
 */
?>


<div class="panel panel-info">
      <div class="panel-heading">
            <h3 class="panel-title">菜单检索</h3>
      </div>
      <div class="panel-body">
      <div class="menu-search">
            <?php $form = ActiveForm::begin([
                'action' => ['index'],
                'method' => 'get',
            ]); ?>
    
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <?= $form->field($model, 'name') ?>
                
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <?= $form->field($model, 'parent') ?>
                
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <?= $form->field($model, 'route') ?>
                
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <?= $form->field($model, 'data') ?>
                
                </div>
             
             <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
             <div class="form-group">
                <?= Html::submitButton(Yii::t('rbac-admin', 'Search'), ['class' => 'btn btn-primary']) ?>
                <?= Html::resetButton(Yii::t('rbac-admin', 'Reset'), ['class' => 'btn btn-default']) ?>
            </div>
             </div>
             





           

            <?php ActiveForm::end(); ?>

            </div>
      </div>
</div>


