<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-05 08:45:57
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-09 06:51:57
 */
use yii\helpers\Html;
use yii\widgets\DetailView;
use diandi\admin\AnimateAsset;
use yii\helpers\Json;
use yii\web\YiiAsset;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\UserGroup */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'User Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
AnimateAsset::register($this);
YiiAsset::register($this);

$opts = Json::htmlEncode([
    'items' => $items,
]);

$this->registerJs("var _opts = {$opts};");
$this->registerJs($this->render('_script.js'));
$animateIcon = ' <i class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></i>';
?>

<?= $this->render('_tab'); ?>
<div class="firetech-main">

<div class="auth-item-view">

    <div class="row">
        <div class="col-sm-11">
        <p>
                    <?= Html::a('更新', ['update', 'id' => $model->id,'module_name'=>$module_name], ['class' => 'btn btn-primary']); ?>
                    <?= Html::a('删除', ['delete', 'id' => $model->id,'module_name'=>$module_name], [
                    'class' => 'btn btn-danger',
                    'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                    ],
                    ]); ?>
                </p>
            <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'id',
                        'name',
                        [
                            'attribute' => 'type',
                            'value' => function ($model) {
                                return $model->type==1?'模块管理员':'系统管理员';
                            },
                        ],                        
                        'description:ntext',
                        // 'created_at',
                        // 'updated_at',
                ],
                'template' => '<tr><th style="width:25%">{label}</th><td>{value}</td></tr>',
            ]);
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-5">
            <input class="form-control search" data-target="available"
                   placeholder="<?=Yii::t('rbac-admin', 'Search for available'); ?>">
            <select multiple size="20" class="form-control list" data-target="available"></select>
        </div>
        <div class="col-sm-1">
            <br><br>
            <?=Html::a('&gt;&gt;'.$animateIcon, ['assign', 'id' => $model->id], [
                'class' => 'btn btn-success btn-assign',
                'data-target' => 'available',
                'title' => Yii::t('rbac-admin', 'Assign'),
            ]); ?><br><br>
            <?=Html::a('&lt;&lt;'.$animateIcon, ['remove', 'id' => $model->id], [
                'class' => 'btn btn-danger btn-assign',
                'data-target' => 'assigned',
                'title' => Yii::t('rbac-admin', 'Remove'),
            ]); ?>
        </div>
        <div class="col-sm-5">
            <input class="form-control search" data-target="assigned"
                   placeholder="<?=Yii::t('rbac-admin', 'Search for assigned'); ?>">
            <select multiple size="20" class="form-control list" data-target="assigned"></select>
        </div>
    </div>
</div>
</div>
