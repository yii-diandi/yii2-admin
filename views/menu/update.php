<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-10 17:01:18
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-10 17:04:41
 */
 

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model diandi/admin\models\Menu */

$this->title = Yii::t('rbac-admin', 'Update Menu') . ': ' . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('rbac-admin', 'Menus'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('rbac-admin', 'Update');
?>
<div class="menu-update">
<?=  $this->render('_tab');   ?>
<div class="firetech-main">

    <?=
        $this->render('_form', [
            'model' => $model,
            'addons' => $addons,

        ])
    ?>

</div>
</div>