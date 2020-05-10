<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-10 16:24:42
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-10 16:27:23
 */
 

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel diandi\admin\models\searchs\MenuTopSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '顶部导航s';
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_tab', [
                    'model' => $model,
                ]) ?>
<div class="firetech-main">

    <div class="menu-top-index ">
                                <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
                <div class="panel panel-default">
            <div class="box-body table-responsive">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'layout'=>"{items}\n{pager}",
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            'id',
                            'name',
                            'mark',
                            'sort',
                            // 'create_time',
                            //'update_time',
                            // 'icon',

                            ['class' => 'common\components\ActionColumn'],
                        ],
                    ]); ?>
                
                
            </div>
        </div>
    </div>
</div>