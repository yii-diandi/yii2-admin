<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel diandi\admin\models\searchs\BlocConfBaiduSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Bloc Conf Baidus';
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_tab') ?>
                
<div class="firetech-main">

    <div class="bloc-conf-baidu-index ">
                                <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
                <div class="panel panel-default">
            <div class="box-body table-responsive">
                                    <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
        'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                                'id',
            'bloc_id',
            'APP_ID',
            'API_KEY',
            'SECRET_KEY',
            //'name',
            //'create_time:datetime',
            //'update_time:datetime',

                    ['class' => 'yii\grid\ActionColumn'],
                    ],
                    ]); ?>
                
                
            </div>
        </div>
    </div>
</div>