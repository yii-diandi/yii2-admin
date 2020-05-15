<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-11 15:43:40
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-15 22:01:54
 */

use common\helpers\ImageHelper;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel diandi\admin\models\searchs\BlocStoreSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '商户管理';
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_tab') ?>
                
<div class="firetech-main">

    <div class="bloc-store-index ">
                                <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
                <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">列表</h3>
            </div>
            <div class="box-body table-responsive">
                                    <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'layout' => "{items}\n{pager}",
                    // 'filterModel' => $searchModel,
                    'columns' => [
                        // ['class' => 'yii\grid\SerialColumn'],
                        'logo' => [
                            'attribute' => 'logo',
                            'format' => ['raw'],
                            'value' => function ($model) {
                                $images = $model->logo;
                                // return $ai_group_status;
                                return Html::img(ImageHelper::tomedia($images), ['width' => 50, 'height' => 50]);
                            },
                        ],
                        'store_id',
                        'name',
                        'bloc.business_name',
                        //'province',
                        //'city',
                        //'address',
                        //'county',
                        //'mobile',
                        //'create_time',
                        //'update_time',
                        //'status',
                        //'lng_lat',

                        ['class' => 'common\components\ActionColumn'],
                    ],
                    ]); ?>
                
                
            </div>
        </div>
    </div>
</div>