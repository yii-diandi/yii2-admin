<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-03 20:07:11
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-03 20:07:13
 */
 

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\searchs\DdMenuCateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '顶部导航';
$this->params['breadcrumbs'][] = $this->title;
?>
<ul class="nav nav-tabs">
    <li>
        <?= Html::a('添加 顶部导航', ['create'], ['class' => '']) ?>
    </li>
    <li  class="active">
        <?= Html::a('顶部导航管理', ['index'], ['class' => 'btn btn-primary']) ?>
    </li>
</ul>
<div class="firetech-main"  style="margin-top:20px;">

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