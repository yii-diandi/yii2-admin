<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-04-13 11:05:44
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-11-07 00:05:35
 */

use common\helpers\ImageHelper;
use kartik\switchinput\SwitchInput;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model diandi/admin\models\User */

$this->title = '修改资料: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<?= $this->render('_tab', [
                    'model' => $model,
                ]) ?>
<div class="firetech-main">
    <div class="panel panel-default">
        <div class="box-body">

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-md-3">

                        <!-- Profile Image -->
                        <div class="box box-primary">
                            <div class="box-body box-profile">
                                <img class="profile-user-img img-responsive img-circle" src="<?= ImageHelper::tomedia($model['avatar']) ?>" alt="User profile picture">

                                <h3 class="profile-username text-center"><?= $model['username'] ?></h3>

                                <p class="text-muted text-center"><?= empty($assign['role'])?'':implode('/', $assign['role']);  ?></p>

                                <ul class="list-group list-group-unbordered">
                                    <li class="list-group-item">
                                        <b>集团</b> 
                                        <span class="pull-right"><?= $business_name ?> </span>
                                    </li>
                                    <li class="list-group-item">
                                        <b>审核状态</b>
                                        <span class="pull-right">
                                            <?= $model['status'] ? '审核通过' : '待审核' ?>
                                        </span>
                                    </li>
                                    <li class="list-group-item">
                                        <b>修改密码</b>
                                        <a class="pull-right"  href="<?= Url::to(['change-pass',
                                        'id'=>$model->id
                                    ]);?>">
                                            修改密码
                                        </a>
                                    </li>
                                </ul>

                                <!-- <a href="#" class="btn btn-primary btn-block"><b>Follow</b></a> -->
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->

                    </div>
                    <!-- /.col -->
                    <div class="col-md-9">
                        <div class="box box-primary">
                            <div class="box-body box-profile">
                                <div class="tab-content">
                                    <div class="active tab-pane" id="settings">
                                        <?= $this->render('_form', [
                                            'model' => $model,
                                        ]) ?>
                                    </div>
                                    <!-- /.tab-pane -->
                                </div>
                                <!-- /.tab-content -->
                            </div>
                        </div>
                        <!-- /.nav-tabs-custom -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </section>
            <!-- /.content -->

        </div>
    </div>
</div>
