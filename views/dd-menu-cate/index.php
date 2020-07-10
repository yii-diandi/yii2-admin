<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-03 20:07:11
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-07-10 21:01:02
 */
 

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\searchs\DdMenuCateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '顶部导航';
$this->params['breadcrumbs'][] = $this->title;
?>
<?= $this->render('_tab'); ?>

<div class="firetech-main">

<div class="dd-menu-cate-index ">
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="panel panel-default">
        <div class="box-body">
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
            'update_time',
            ['class' => 'common\components\ActionColumn'],

        ],
    ]); ?>


</div>
    </div>
</div>
</div>