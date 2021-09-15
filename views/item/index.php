<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-03 15:12:58
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-05-19 14:55:26
 */
 

use yii\helpers\Html;
use common\widgets\MyGridView;
use diandi\admin\components\RouteRule;
use diandi\admin\components\Configs;
use common\widgets\MyTreeGrid;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel diandi/admin\models\searchs\AuthItem */
/* @var $context diandi/admin\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$this->title = Yii::t('rbac-admin', $labels['Items']);
$this->params['breadcrumbs'][] = $this->title;

$rules = array_keys(Configs::authManager()->getRules());
$rules = array_combine($rules, $rules);
unset($rules[RouteRule::RULE_NAME]);
?>

<?= $this->render('_tab'); ?>
<div class="firetech-main">
<?php  echo $this->render('_search', ['model' => $searchModel]); ?>


<div class="panel panel-default">
      <div class="panel-heading">
            <h3 class="panel-title">权限列表</h3>
      </div>
      <div class="box-body">

      
    

<div class="role-index">
<?= MyTreeGrid::widget([
                    'dataProvider' => $dataProvider,
                    'keyColumnName' => 'id',
                    'parentColumnName' => 'parent_id',
                    'parentRootValue' => '0',
                    'pluginOptions' => [
                        'initialState' => 'collapsed',
                        // 'expanderTemplate'=> '<span>&nbsp&nbsp&nbsp&nbsp|---</span>',

                    ],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        // [
                        //     'attribute' => 'parent_id',
                        //     'label' => Yii::t('rbac-admin', 'parent_id'),
                        // ],
                        [
                            'attribute' => 'name',
                            'label' => Yii::t('rbac-admin', 'Name'),
                        ],
                        [
                            'attribute' => 'description',
                            'label' => Yii::t('rbac-admin', 'Description'),
                        ],
                        [
                            'attribute' => 'rule_name',
                            'label' => Yii::t('rbac-admin', 'Rule Name'),
                            // 'filter' => $rules
                        ],
                        
                        [
                            'class' => 'common\components\ActionColumn',
                            'urlCreator' => function ($action, $model, $key, $index) {
                                switch($action)
                                {
                                    case 'delete':
                                        return Url::to(['delete','id'=>$model['id'],'module_name'=>$model['module_name']]);
                                    break;
                                    case 'view':
                                        return Url::to(['view','id'=>$model['id'],'module_name'=>$model['module_name']]);
                                    
                                    break;
                                    case 'update':
                                        return Url::to(['update','id'=>$model['id'],'module_name'=>$model['module_name']]);
                                    break;
                                }
                        
                            },
                        ],
                    ],
                ]);

                ?>
</div>

</div>
</div>

</div>
