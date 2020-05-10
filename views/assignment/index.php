<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-09 08:38:52
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-10 18:38:25
 */

use diandi\admin\models\Bloc;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel diandi/admin\models\searchs\Assignment */
/* @var $usernameField string */
/* @var $extraColumns string[] */

$this->title = Yii::t('rbac-admin', 'Assignments');
$this->params['breadcrumbs'][] = $this->title;
$Bloc = new Bloc();
$blocs = $Bloc->find()->indexBy('bloc_id')->asArray()->all();
$Bloc = new Bloc();
$blocs = $Bloc->find()->indexBy('bloc_id')->asArray()->all();
$columns = [
    ['class' => 'yii\grid\SerialColumn'],
    $usernameField,
    'email',
    [
        'attribute' => 'bloc_id',
        'value' => function ($model) use ($blocs) {
            if($model->bloc_id){
                return $blocs[$model->bloc_id];                
            }else{
                return '未分配';
            }
        },
        
    ],
    [
         'attribute' => 'store_id',
        'value' => function ($model) use ($stores) {
            if($model->store_id){
                return $stores[$model->store_id];                
            }else{
                return '未分配';
            }
        },
    ],
];
if (!empty($extraColumns)) {
    $columns = array_merge($columns, $extraColumns);
}
$columns[] = [
    'class' => 'common\components\ActionColumn',
//    'class' => 'yii\grid\ActionColumn',
    'template' => '{view}',
    'buttons' => [
        'view' => function ($url, $model, $key) use ($module_name) {
            $url = Url::to(['view','id'=>$model->id,'module_name'=>$module_name]);
            
            return  Html::a('<button type="button" class="btn btn-primary btn-sm">权限分配</button>', $url, ['title' => '权限分配']);
        },
    ],
];
?>
<div class="assignment-index">
    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
   
    
    <div class="panel panel-default">
          <div class="panel-heading">
                <h3 class="panel-title">管理员列表</h3>
          </div>
          <div class="panel-body">
                
            <?php Pjax::begin(); ?>
                <?=
                GridView::widget([
                    'layout'=>"{items}\n{pager}",
                    'dataProvider' => $dataProvider,
                    // 'filterModel' => $searchModel,
                    'columns' => $columns,
                    
                ]);
                ?>
            <?php Pjax::end(); ?>
          </div>
    </div>
    
    

</div>
