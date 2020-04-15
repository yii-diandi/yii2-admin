<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-31 06:45:37
 * @Last Modified by:   Wang Chunsheng 2192138785@qq.com
 * @Last Modified time: 2020-04-05 14:40:20
 */

use backend\modules\bloc\models\Bloc;
use common\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\modules\bloc\models\searchs\BlocSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '公司';
$this->params['breadcrumbs'][] = $this->title;
?>
<ul class="nav nav-tabs">

    <li class="active">
        <?= Html::a('公司管理', ['index'], ['class' => 'btn btn-primary']) ?>
    </li>
    <li>
        <?= Html::a('添加公司', ['create'], ['class' => '']) ?>
    </li>
</ul>
<div class="firetech-main">

    <div class="bloc-index ">
        <?php echo $this->render('_search', ['model' => $searchModel]);  ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">公司列表</h3>
            </div>
            <div class="box-body table-responsive">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    // 'filterModel' => $searchModel,
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

                        ['class' => 'yii\grid\ActionColumn'],
                    ],
                ]); ?>


            </div>
        </div>
    </div>
</div>