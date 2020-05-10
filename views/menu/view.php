<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-10 17:01:21
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-10 17:04:38
 */
 

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model diandi/admin\models\Menu */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('rbac-admin', 'Menus'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-view">

<?=  $this->render('_tab');   ?>
<div class="firetech-main">

    <p>
        <?= Html::a(Yii::t('rbac-admin', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?=
        Html::a(Yii::t('rbac-admin', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ])
        ?>
    </p>

    <?=
    DetailView::widget([
        'model' => $model,
        'attributes' => [
            'menuParent.name:text:Parent',
            'name',
            'route',
            'order',
        ],
    ])
    ?>

</div>
</div>
