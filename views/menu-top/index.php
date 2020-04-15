<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel diandi\admin\models\searchs\MenuTopSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '顶部导航s';
$this->params['breadcrumbs'][] = $this->title;
?>
<ul class="nav nav-tabs">
    <li>
        <?= Html::a('添加顶部导航', ['create'], ['class' => '']) ?>
    </li>
    <li class="active">
        <?= Html::a('顶部导航管理', ['index'], ['class' => 'btn btn-primary']) ?>
    </li>
</ul>
<div class="firetech-main">

    <div class="menu-top-index ">
                                <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
                <div class="panel panel-default">
            <div class="box-body table-responsive">
                                    <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
        'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                                'id',
            'name',
            'mark',
            'sort',
            'create_time',
            //'update_time',
            //'icon',

                    ['class' => 'yii\grid\ActionColumn'],
                    ],
                    ]); ?>
                
                
            </div>
        </div>
    </div>
</div>