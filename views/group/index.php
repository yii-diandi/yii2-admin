<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-04 15:12:23
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-05-24 15:51:31
 */

use yii\helpers\Html;
use common\widgets\MyGridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel diandi\admin\models\searchs\UserGroupSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '用户组管理';
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_tab') ?>
                
<div class="firetech-main">

    <div class="user-group-index ">
        <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">列表</h3>
            </div>
            <div class="box-body table-responsive">
                <?= MyGridView::widget([
                    'dataProvider' => $dataProvider,
                    'layout' => "{items}\n{pager}",
                    // 'filterModel' => $searchModel,
                    'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                                        'id',
                            'name',
                            'type',
                            'description:ntext',
                            // 'created_at',
                            //'updated_at',
                            [
                                'class' => 'common\components\ActionColumn',                                
                                'urlCreator'=>function($action,$model,$key,$index){
                                    switch($action)
                                    {
                                            case'delete':
                                            return Url::to(['delete',
                                                'id'=>$model['id'],
                                                'name'=>$model['name'],
                                                'module_name'=>$model['module_name']
                                            ]);
                                            
                                            break;
                                            
                                            case'view':
                                                
                                            return Url::to(['view',
                                                'id'=>$model['id'],
                                                'name'=>$model['name'],
                                                'module_name'=>$model['module_name']
                                            ]);
                                           
                                            
                                            break;
                                            
                                            case'update':
                                                
                                            return Url::to(['update',
                                                'id'=>$model['id'],
                                                'name'=>$model['name'],
                                                'module_name'=>$model['module_name']
                                            ]);
                                           
                                            
                                            break;
                                    
                                    }
                                }
    
                            
                            ],
                    ],
                ]); ?>
                
                
            </div>
        </div>
    </div>
</div>