<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel diandi\admin\models\searchs\MenuTopSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Menu Tops';
$this->params['breadcrumbs'][] = $this->title;
?>
<ul class="nav nav-tabs">
    <li>
        <?= Html::a('添加 Menu Top', ['create'], ['class' => '']) ?>
    </li>
    <li class="active">
        <?= Html::a('Menu Top管理', ['index'], ['class' => 'btn btn-primary']) ?>
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