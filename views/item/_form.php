<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-03 15:44:25
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-08 23:42:34
 */
 

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use diandi\admin\components\RouteRule;
use diandi\admin\AutocompleteAsset;
use yii\helpers\Json;
use diandi\admin\components\Configs;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $model diandi/admin\models\AuthItem */
/* @var $form yii\widgets\ActiveForm */
/* @var $context diandi/admin\components\ItemController */

use yii\rbac\Permission;

$context = $this->context;
$labels = $context->labels();
$rules = Configs::authManager()->getRules();
$items = Configs::authManager()->getParentItem($module_name=='sys'?0:1,$module_name);
unset($rules[RouteRule::RULE_NAME]);
$rule = Json::htmlEncode(array_keys($rules));
$item = Json::htmlEncode($items);
$this->registerJs("var _rule = $rule,_item = $item;");
$this->registerJs($this->render('_create.js'));
AutocompleteAsset::register($this);
?>

<div class="auth-item-form">
    <?php $form = ActiveForm::begin(['id' => 'item-form']); ?>
    <div class="row">
        <div class="col-sm-6">
            <?= Html::activeHiddenInput($model,'module_name',array('value'=>$module_name)) ?>
            <?= Html::activeHiddenInput($model,'type',array('value'=>$module_name=='sys'?0:1)) ?>
            <?= Html::activeHiddenInput($model,'parent_id',array('value'=>$model->parent_id,'id'=>'parent_id')) ?>
           
            <?= $form->field($model, 'name')->textInput(['maxlength' => 64]) ?>
            
           <?= $form->field($model, 'parent_name')->textInput([
                'id' => 'parent_name'
            ]) ?>          

            <?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'ruleName')->textInput(['id' => 'rule_name']) ?>

            <?= $form->field($model, 'data')->textarea(['rows' => 6]) ?>
        </div>
    </div>
    <div class="form-group">
        <?php
        echo Html::submitButton($model->isNewRecord ? Yii::t('rbac-admin', 'Create') : Yii::t('rbac-admin', 'Update'), [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
            'name' => 'submit-button'])
        ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
