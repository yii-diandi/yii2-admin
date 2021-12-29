<?php

/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-06 17:24:15
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-06-21 14:06:53
 */


use diandi\admin\AnimateAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\YiiAsset;

/* @var $this yii\web\View */
/* @var $model diandi/admin\models\Assignment */
/* @var $fullnameField string */

$userName = $model->{$usernameField};
if (!empty($fullnameField)) {
    $userName .= ' (' . ArrayHelper::getValue($model, $fullnameField) . ')';
}
$userName = Html::encode($userName);

$this->title = Yii::t('rbac-admin', 'Assignment') . ' : ' . $userName;

$this->params['breadcrumbs'][] = ['label' => Yii::t('rbac-admin', 'Assignments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $userName;

AnimateAsset::register($this);
YiiAsset::register($this);
$opts = Json::htmlEncode([
    'items' => $items,
]);
$this->registerJs("var _opts = {$opts};");
$this->registerJs($this->render('_script.js'));
$animateIcon = ' <i class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></i>';
?>
<div class="firetech-main">

    <p>
        <?= Html::a('返回管理员列表', ['/admin/user/index'], ['class' => 'btn btn-primary']); ?>

    </p>

    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title"><?= $this->title; ?></h3>
        </div>
        <div class="panel-body">
            <div class="assignment-index">
                <div class="row">
                    <div class="col-sm-5">
                        <input class="form-control search" data-target="available" placeholder="<?= Yii::t('rbac-admin', 'Search for available'); ?>">
                        <select multiple size="20" class="form-control list" data-target="available">
                        </select>
                    </div>
                    <div class="col-sm-1">
                        <br><br>
                        <?= Html::a('&gt;&gt;' . $animateIcon, ['assign', 'id' => (string) $model->id, 'module_name' => $module_name], [
                            'class' => 'btn btn-success btn-assign',
                            'data-target' => 'available',
                            'title' => Yii::t('rbac-admin', 'Assign'),
                        ]); ?><br><br>
                        <?= Html::a('&lt;&lt;' . $animateIcon, ['revoke', 'id' => (string) $model->id, 'module_name' => $module_name], [
                            'class' => 'btn btn-danger btn-assign',
                            'data-target' => 'assigned',
                            'title' => Yii::t('rbac-admin', 'Remove'),
                        ]); ?>
                    </div>
                    <div class="col-sm-5">
                        <input class="form-control search" data-target="assigned" placeholder="<?= Yii::t('rbac-admin', 'Search for assigned'); ?>">
                        <select multiple size="20" class="form-control list" data-target="assigned">
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>