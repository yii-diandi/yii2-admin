<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-04-12 13:46:59
 * @Last Modified by:   Wang Chunsheng 2192138785@qq.com
 * @Last Modified time: 2020-04-12 18:32:05
 */

use diandi\admin\components\Helper;
use yii\helpers\Html;
use yii\grid\GridView;

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
                            'class' => 'yii\grid\ActionColumn',
                            'template' => Helper::filterActionColumn(['view', 'activate', 'update','delete']),
                            'buttons' => [
                                'activate' => function($url, $model) {
                                    if ($model->status == 10) {
                                        return '';
                                    }
                                    $options = [
                                        'title' => Yii::t('rbac-admin', 'Activate'),
                                        'aria-label' => Yii::t('rbac-admin', 'Activate'),
                                        'data-confirm' => Yii::t('rbac-admin', 'Are you sure you want to activate this user?'),
                                        'data-method' => 'post',
                                        'data-pjax' => '0',
                                    ];
                                    return Html::a('<span class="glyphicon glyphicon-ok"></span>', $url, $options);
                                }
                                ]
                        ],
                    ],
                ]); ?>


            </div>
        </div>
    </div>