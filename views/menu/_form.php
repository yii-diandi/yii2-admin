<?php
/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-28 11:46:12
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-03-01 12:07:54
 */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use diandi\admin\models\Menu;
use yii\helpers\Json;
use diandi\admin\AutocompleteAsset;
use diandi\admin\models\MenuTop;
use yii\helpers\ArrayHelper;

$menucate = MenuTop::find()->orderBy('sort')->asArray()->all();

/* @var $this yii\web\View */
/* @var $model diandi/admin\models\Menu */
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
    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => 128]); ?>

            <?= $form->field($model, 'parent')
                ->dropDownList(ArrayHelper::map($parentMenu, 'id', 'name'), [
                    'prompt' => [
                        'text' => '顶级导航',
                        'options' => ['value' => 0],
                    ],
                ])
                ->label('父级菜单'); ?>
                
            <?= $form->field($model, 'route')->textInput(['id' => 'route']); ?>
            <?= $form->field($model, 'type')->dropDownList(ArrayHelper::map($menucate, 'mark', 'name')); ?>


        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'order')->input('number'); ?>
            <?= $form->field($model, 'icon')->widget('common\widgets\adminlte\Icon', ['options' => [
                'label' => '选择图标',
            ]]); ?>
            <?= $form->field($model, 'is_sys')->dropDownList(['system' => '系统菜单', 'addons' => '模块菜单']); ?>

            <?= $form->field($model, 'module_name')->dropDownList(ArrayHelper::map($addons, 'name', 'title'), ['prompt' => '请选择']); ?>
        </div>
        <div class="col-sm-12">
            <?= $form->field($model, 'data')->textarea(['rows' => 4]); ?>
            <?=
                Html::submitButton($model->isNewRecord ? Yii::t('rbac-admin', 'Create') : Yii::t('rbac-admin', 'Update'), ['class' => $model->isNewRecord
                    ? 'btn btn-success' : 'btn btn-primary', ]);
            ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
