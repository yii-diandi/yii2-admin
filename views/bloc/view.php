<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-31 06:41:22
 * @Last Modified by:   Wang Chunsheng 2192138785@qq.com
 * @Last Modified time: 2020-04-05 14:41:22
 */


use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\modules\bloc\models\Bloc */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Blocs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<ul class="nav nav-tabs">
    <li>
        <?= Html::a('公司管理', ['index'], ['class' => '']) ?>
    </li>
    <li>
        <?= Html::a('添加公司', ['create'], ['class' => '']) ?>
    </li>
    <li class="active">
        <?= Html::a('公司详情', ['view'], ['class' => '']) ?>
    </li>
</ul>
<div class=" firetech-main">
    <div class="bloc-view">

        <div class="panel panel-default">
            <div class="box-body">

                <p>
                    <?= Html::a('更新', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('删除', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Are you sure you want to delete this item?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </p>

                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'bloc_id',
                        'business_name',
                        'pid',
                        'category',
                        'province',
                        'city',
                        'district',
                        'address',
                        'longitude',
                        'latitude',
                        'telephone',
                        'avg_price',
                        'recommend',
                        'special',
                        'introduction',
                        'open_time',
                        'status',
                        'sosomap_poi_uid',
                        'license_no',
                        'license_name',
                        'other_files:ntext',
                    ],
                ]) ?>

            </div>
        </div>
    </div>
</div>