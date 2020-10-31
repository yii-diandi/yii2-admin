<?php

use yii\helpers\Html;
use common\widgets\MyGridView;

/* @var $this yii\web\View */
/* @var $searchModel common\addons\diandi_operator\modules\searchs\OperatorBlocSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Operator Blocs';
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_tab') ?>
                
<div class="firetech-main">

    <div class="operator-bloc-index ">
                                <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
                <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">列表</h3>
            </div>
            <div class="box-body table-responsive">
                                    <?= MyGridView::widget([
                    'dataProvider' => $dataProvider,
                    'layout' => "{items}\n{pager}",
                    'filterModel' => $searchModel,
        'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                                'id',
            'bloc_id',
            'store_id',
            'profit1',
            'profit2',
            //'profit3',
            //'name',
            //'account',
            //'money',
            //'earnings',
            //'create_time:datetime',
            //'update_time:datetime',

                    ['class' => 'yii\grid\ActionColumn'],
                    ],
                    ]); ?>
                
                
            </div>
        </div>
    </div>
</div>