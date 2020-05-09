<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-09 10:20:07
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-09 10:20:20
 */
 

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model diandi/admin\models\User */

$this->title = '添加 User';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_tab', [
                    'model' => $model,
                ]) ?>
<div class="firetech-main">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="user-create">

                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>

            </div>
        </div>
    </div>
</div>