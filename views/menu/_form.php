<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\models\Menu;
use yii\helpers\Json;
use mdm\admin\AutocompleteAsset;
use common\models\DdMenuCate;
use yii\helpers\ArrayHelper;

$menucate = DdMenuCate::find()->orderBy('sort')->asArray()->all();

/* @var $this yii\web\View */
/* @var $model mdm\admin\models\Menu */
/* @var $form yii\widgets\ActiveForm */
AutocompleteAsset::register($this);
$opts = Json::htmlEncode([
    'menus' => Menu::getMenuSource(),
    'routes' => Menu::getSavedRoutes(),
]);
$this->registerJs("var _opts = $opts;");
$this->registerJs($this->render('_script.js'));
?>

<div class="menu-form">
    <?php $form = ActiveForm::begin(); ?>
    <?= Html::activeHiddenInput($model, 'parent', ['id' => 'parent_id']); ?>
    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => 128]) ?>

            <?= $form->field($model, 'parent_name')->textInput(['id' => 'parent_name']) ?>

            <?= $form->field($model, 'route')->textInput(['id' => 'route']) ?>
            <?= $form->field($model, 'type')->dropDownList(ArrayHelper::map($menucate, 'mark', 'name')) ?>

        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'order')->input('number') ?>
            <?= $form->field($model, 'icon')->textInput(['id' => 'icon']) ?>
            <?= $form->field($model, 'is_sys')->dropDownList(['system' => '系统菜单', 'addons' => '模块菜单']) ?>

            <?= $form->field($model, 'module_name')->dropDownList(ArrayHelper::map($addons, 'name', 'title'), ['prompt' => '请选择']) ?>
        </div>

    </div>
    <div class="row">
        <div class="col-sm-12">
            <?= $form->field($model, 'data')->textarea(['rows' => 4]) ?>

        </div>
        <div class="form-group">
            <?=
                Html::submitButton($model->isNewRecord ? Yii::t('rbac-admin', 'Create') : Yii::t('rbac-admin', 'Update'), ['class' => $model->isNewRecord
                    ? 'btn btn-success' : 'btn btn-primary'])
            ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>