<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-03 15:13:58
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-02-23 18:46:34
 */
 

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model diandi/admin\models\AuthItem */
/* @var $context diandi/admin\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$this->title = Yii::t('rbac-admin', 'Update ' . $labels['Item']) . ': ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('rbac-admin', $labels['Items']), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->name]];
$this->params['breadcrumbs'][] = Yii::t('rbac-admin', 'Update');
?>
<?= $this->render('_tab'); ?>

<div class="firetech-main">

    <div class="auth-item-update">
        <?=
        $this->render('_form', [
            'addons' => $addons,
            'module_name' => $module_name,
            'model' => $model,
            'parentItem' => $parentItem
            
        ]);
        ?>
    </div>
</div>
