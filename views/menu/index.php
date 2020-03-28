<?php
/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-28 16:18:52
 * @Last Modified by:   Wang Chunsheng 2192138785@qq.com
 * @Last Modified time: 2020-03-28 17:49:27
 */
 

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use leandrogehlen\treegrid\TreeGrid;
use yii2mod\editable\EditableColumn;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel diandi/admin\models\searchs\Menu */

$this->title = Yii::t('rbac-admin', 'Menus');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]);  
    ?>

    <p>
        <?= Html::a(Yii::t('rbac-admin', 'Create Menu'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= TreeGrid::widget([
        'dataProvider' => $dataProvider,
        'keyColumnName' => 'id',
        'parentColumnName' => 'parent',
        'parentRootValue' => null, //first parentId value
        'pluginOptions' => [
            'initialState' => 'collapsed',
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            // 'id',
            // 'parent',
            [
                'class' => EditableColumn::class,
                'attribute' => 'name',
                'url' => ['update-files']
            ],
            'route',
            // 'order',
            [
                'class' => EditableColumn::class,
                'attribute' => 'order',
                'url' => ['update-files']
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ]
    ]);

    ?>


</div>