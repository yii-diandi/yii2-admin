<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-10 17:01:12
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-02-23 17:57:19
 */
 

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model diandi/admin\models\Menu */

$this->title = Yii::t('rbac-admin', 'Create Menu');
$this->params['breadcrumbs'][] = ['label' => Yii::t('rbac-admin', 'Menus'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-create">
<?=  $this->render('_tab');   ?>
<div class="firetech-main">


    <?=
        $this->render('_form', [
            'model' => $model,
            'addons' => $addons,
            'parentMenu' => $parentMenu
        ])
    ?>

</div>
</div>