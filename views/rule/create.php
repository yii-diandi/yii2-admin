<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-10 16:06:49
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-10 16:18:28
 */
 

use yii\helpers\Html;

/* @var $this  yii\web\View */
/* @var $model diandi/admin\models\BizRule */

$this->title = Yii::t('rbac-admin', 'Create Rule');
$this->params['breadcrumbs'][] = ['label' => Yii::t('rbac-admin', 'Rules'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
 <?=
    $this->render('_tab', [
        'model' => $model,
    ]);
    ?>
<div class="firetech-main">

<div class="auth-item-create">


    <?=
    $this->render('_form', [
        'model' => $model,
    ]);
    ?>

</div>
</div>
