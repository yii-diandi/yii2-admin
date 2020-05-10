<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-10 16:07:21
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-10 16:11:08
 */
 

use yii\helpers\Html;

/* @var $this  yii\web\View */
/* @var $model diandi/admin\models\BizRule */

$this->title = Yii::t('rbac-admin', 'Update Rule') . ': ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('rbac-admin', 'Rules'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->name]];
$this->params['breadcrumbs'][] = Yii::t('rbac-admin', 'Update');
?>
<div class="auth-item-update">

<?=
    $this->render('_tab', [
        'model' => $model,
    ]);
    ?>
<div class="firetech-main">

    <?=
    $this->render('_form', [
        'model' => $model,
    ]);
    ?>
</div>
</div>
