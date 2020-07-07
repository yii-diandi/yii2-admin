<?php
/**
 * @Author: Wang chunsheng
 * @Date:   2020-04-29 16:06:59
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-07-07 08:39:25
 */
use leandrogehlen\treegrid\TreeGrid;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\bloc\models\searchs\BlocSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '公司';
$this->params['breadcrumbs'][] = $this->title;
?>
<ul class="nav nav-tabs">

    <li class="active">
        <?= Html::a('公司管理', ['index'], ['class' => 'btn btn-primary']); ?>
    </li>
    <li>
        <?= Html::a('添加公司', ['create'], ['class' => '']); ?>
    </li>
</ul>
<div class="firetech-main">

    <div class="bloc-index ">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">公司列表</h3>
            </div>
            <div class="box-body table-responsive">
            <?= TreeGrid::widget([
                    'dataProvider' => $dataProvider,
                    'keyColumnName' => 'bloc_id',
                    'parentColumnName' => 'pid',
                    'parentRootValue' => '0', //first parentId value
                    'pluginOptions' => [
                        'initialState' => 'collapsed',
                    ],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'bloc_id',
                        'business_name',
                        'pid',
                        'category',
                        'province',
                        //'city',
                        //'district',
                        //'address',
                        //'longitude',
                        //'latitude',
                        //'telephone',
                        //'photo_list',
                        //'avg_price',
                        //'recommend',
                        //'special',
                        //'introduction',
                        //'open_time',
                        //'location_id',
                        //'status',
                        //'source',
                        //'message',
                        //'sosomap_poi_uid',
                        //'license_no',
                        //'license_name',
                        //'other_files:ntext',
                        //'audit_id',
                        //'on_show',

                        ['class' => 'common\components\ActionColumn'],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => '操作',
                            'template' => '{user}{management}{stores}',
                            'buttons' => [
                                'stores' => function ($url, $model, $key) {
                                    $url = Url::to(['/admin/store/index', 'bloc_id' => $model['bloc_id']]);

                                    return  Html::a('商户管理', $url, [
                                        'title' => '商户管理',
                                        'class' => 'btn btn-default',
                                        // 'data' => [
                                        //     'confirm' => Yii::t('app', '确认卸载该模块吗?'),
                                        //     'method' => 'post',
                                        // ]
                                    ]);
                                },
                                'user' => function ($url, $model, $key) {
                                    $url = Url::to(['user-bloc/index', 'bloc_id' => $model['bloc_id']]);

                                    return  Html::a('管理员', $url, [
                                        'title' => '管理员',
                                        'class' => 'btn btn-default',
                                        // 'data' => [
                                        //     'confirm' => Yii::t('app', '确认卸载该模块吗?'),
                                        //     'method' => 'post',
                                        // ]
                                    ]);
                                },
                                'management' => function ($url, $model, $key) {
                                    $url = Url::to(['setting/baidu', 'bloc_id' => $model['bloc_id']]);

                                    return  Html::a('参数配置', $url, [
                                        'title' => '进入模块',
                                        'class' => 'btn btn-default',
                                        // 'data' => [
                                        //     'confirm' => Yii::t('app', '确认卸载该模块吗?'),
                                        //     'method' => 'post',
                                        // ]
                                    ]);
                                },
                            ],
                            'contentOptions' => ['class' => 'btn-group'],
                            // 'buttons' => [],
                            'headerOptions' => ['width' => '250px'],
                        ],
                    ],
                ]);

                ?>
      
            </div>
        </div>
    </div>
</div>