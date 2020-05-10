<?php
/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-28 16:18:52
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-10 17:05:28
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
<?=  $this->render('_tab');   ?>
<div class="firetech-main">

    <?= $this->render('_search', ['model' => $searchModel]);  
    ?>

<div class="panel panel-default">
      <div class="panel-heading">
            <h3 class="panel-title">菜单列表</h3>
      </div>
      <div class="panel-body">
         

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
            ['class' => 'common\components\ActionColumn'],
        ]
    ]);

    ?>

      </div>
</div>
</div>
</div>