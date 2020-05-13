<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-11 15:15:03
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-11 18:54:44
 */

use common\models\DdRegion;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\BlocStore */
/* @var $form yii\widgets\MyActiveForm */
?>

<div class="bloc-store-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
    <?= Html::activeHiddenInput($model,'bloc_id',array(
        'value'=> Yii::$app->request->get('bloc_id',0) 
    )) ?>
    
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'thumb')->widget('manks\FileInput', [])->hint('尺寸：500px*500px'); ?>
    
    <?= $form->field($model, 'images')->widget('manks\FileInput', [
        'clientOptions' => [
            'pick' => [
                'multiple' => true,
            ],
        ]])->hint('尺寸：375px*500px'); 
    ?>
    <?= $form->field($model, 'lng_lat')->textInput(['maxlength' => true]) ?>
        
    </div>
    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">

    <?= $form->field($model, '地区')->widget(\diandi\region\Region::className(), [
        'model'=>$model,
        'url'=> \yii\helpers\Url::toRoute(['get-region']),
        'province'=>[
            'attribute'=>'province',
            'items'=>DdRegion::getRegion(),
            'options'=>[
                'class'=>'form-control form-control-inline',
                'prompt'=>'选择省份'
            ]
        ],
        'city'=>[
            'attribute'=>'city',
            'items'=>DdRegion::getRegion($model['province']),
            'options'=>[
                'class'=>'form-control form-control-inline',
                'prompt'=>'选择城市',
                'style'=>'margin-top:10px;'
            ]
        ],
        'district'=>[
            'attribute'=>'county',
            'items'=>DdRegion::getRegion($model['city']),
            'options'=>[
                'class'=>'form-control form-control-inline',
                'prompt'=>'选择县/区',
                'style'=>'margin-top:10px;'
                
            ]
        ]
    ]);
    ?>
    
     
    <?= $form->field($model, 'mobile')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'status')->radioList([
            1 => '审核通过',
            2 => '审核中',
            3 => '审核未通过',
        ]); ?>

    </div>

   
   <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
   <div class="form-group">
        <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
    </div>
   </div>
   

  

   

    <?php ActiveForm::end(); ?>

</div>
