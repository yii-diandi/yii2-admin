<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-09 08:38:52
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-07-10 21:10:37
 */
 

use yii\helpers\Html;
use common\widgets\MyGridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel diandi/admin\models\searchs\Assignment */
/* @var $usernameField string */
/* @var $extraColumns string[] */

$this->title = Yii::t('rbac-admin', 'Assignments');
$this->params['breadcrumbs'][] = $this->title;

$columns = [
    ['class' => 'yii\grid\SerialColumn'],
    $usernameField,
];
if (!empty($extraColumns)) {
    $columns = array_merge($columns, $extraColumns);
}
$columns[] = [
    'class' => 'common\components\ActionColumn',
//    'class' => 'yii\grid\ActionColumn',
    'template' => '{view}',
    'urlCreator'=>function($action,$model,$key,$index) use ($module_name){
        switch($action)
        {
            case'view':
            
                return Url::to(['view','id'=>$model->id,'module_name'=>$module_name]);
            
            break;
        }
    },
    
];
?>
<div class="firetech-main">

    <div class="assignment-index">
        <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    
        
        <div class="panel panel-default">
            <div class="panel-heading">
                    <h3 class="panel-title">管理员列表</h3>
            </div>
            <div class="panel-body">
                    
                <?php Pjax::begin(); ?>
                    <?=
                    MyGridView::widget([
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
</div>
