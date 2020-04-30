<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-04-30 20:53:43
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-04-30 20:54:03
 */

/***
 * @开源软件: 店滴AI-基于AI的软硬件开源解决方案
 * @官方地址: http://www.wayfirer.com/
 * @版本: 1.0
 * @邮箱: 2192138785@qq.com
 * @作者: Wang Chunsheng
 * @Date: 2020-02-28 22:38:39
 * @LastEditTime: 2020-04-25 19:09:17
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $model \app\models\forms\ConfigurationForm */
/* @var $this \yii\web\View */

$this->title = Yii::t('app', '小程序设置');
?>


<?php echo $this->renderAjax('table'); ?>

<div class="firetech-main"  style="margin-top:20px;">
    <div class="panel panel-default">
        <div class="box-body">
            <div class="dd-member-create">

                <?php $form = ActiveForm::begin(); ?>

                <?php echo $form->field($model, 'name'); ?>

                <?php echo $form->field($model, 'description'); ?>
                <?php echo $form->field($model, 'original'); ?>
                <?php echo $form->field($model, 'AppId'); ?>
                <?php echo $form->field($model, 'AppSecret'); ?>
                <?= $form->field($model, 'headimg')->widget('manks\FileInput', []); ?>
                <div class="form-group">
                        <?= Html::submitButton('保存', ['class' => 'btn btn-primary']); ?>
                        <?= Html::a('返回公司', Url::to(['bloc/index']), ['class' => 'btn btn-primary']); ?>
                    </div>
                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>

