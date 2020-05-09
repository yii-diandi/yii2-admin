<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-04-12 13:46:59
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-09 20:22:12
 */

use diandi\admin\components\Helper;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel diandi/admin\models\searchs\User */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '管理员管理';
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_tab', []) ?>
<div class="firetech-main">

    <div class="user-index ">
        <?php echo $this->render('_search', ['model' => $searchModel]);
        ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    // 'filterModel' => $searchModel,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],

                        'id',
                        'username',
                        'addonsUser.user_id',
                        //            'auth_key',
                        //            'password_hash',
                        //            'password_reset_token',
                        'email:email',
                        // 'status',
                        [
                            'attribute' => 'status',
                            'value' => function($model) {
                                return $model->status == 0 ? 'Inactive' : 'Active';
                            },
                            'filter' => [
                                0 => 'Inactive',
                                10 => 'Active'
                            ]
                        ],
                        //'created_at',
                        //'updated_at',
                        //'verification_token',
                        //'avatar',

                        [
                            'class' => 'common\components\ActionColumn',
                            // 'class' => 'yii\grid\ActionColumn',
                            // 'template' => Helper::filterActionColumn(['view', 'activate', 'update','delete']),
                            'template' =>"{view}{activate}{update} {delete}{authedit}",
                            'buttons' => [
                                'activate' => function($url, $model) {
                                    if ($model->status == 10) {
                                        return '';
                                    }
                                    $options = [
                                        'title' => Yii::t('rbac-admin', 'Activate'),
                                        'class'=>'btn btn-default btn-ac',
                                        'aria-label' => Yii::t('rbac-admin', 'Activate'),
                                        'data-confirm' => Yii::t('rbac-admin', 'Are you sure you want to activate this user?'),
                                        'data-method' => 'post',
                                        'data-pjax' => '0',
                                    ];
                                    return Html::a('<span class="glyphicon glyphicon-ok"></span>', $url, $options);
                                },
                                'authedit' => function($url, $model) use ($module_name) {
                                    $url = Url::to(['/admin/assignment/view',
                                        'id'=>$model->id,
                                        'module_name'=>$module_name,
                                        
                                    ]);
                                    
                                    $options = [
                                        'title' => '权限分配',
                                        'class'=>'btn btn-default btn-ac',
                                        // 'aria-label' => Yii::t('rbac-admin', 'Activate'),
                                        // 'data-confirm' => Yii::t('rbac-admin', 'Are you sure you want to activate this user?'),
                                        // 'data-method' => 'post',
                                        // 'data-pjax' => '0',
                                    ];
                                    return Html::a('<span class="fa fa-fw fa-user-plus"></span>权限分配', $url, $options);
                                }
                            ],                           
                            'urlCreator'=>function($action,$model,$key,$index) use($module_name){
                                switch($action)
                                {
                                        case'delete':
                                        return Url::to(['delete',
                                            'id'=>$model->id,
                                            'module_name'=>$module_name
                                        ]);
                                        
                                        break;
                                        
                                        case'view':
                                            
                                        return Url::to(['view',
                                            'id'=>$model->id,
                                            'module_name'=>$module_name
                                        ]);
                                       
                                        
                                        break;
                                        
                                        case'update':
                                            
                                        return Url::to(['update',
                                            'id'=>$model->id,
                                            'module_name'=>$module_name
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