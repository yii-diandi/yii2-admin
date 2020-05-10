<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-10 16:07:33
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-10 16:12:53
 */
 

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var diandi/admin\models\AuthItem $model
 */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('rbac-admin', 'Rules'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-item-view">
<?=
    $this->render('_tab', [
        'model' => $model,
    ]);
    ?>
<div class="firetech-main">

    <p>
        <?= Html::a(Yii::t('rbac-admin', 'Update'), ['update', 'id' => $model->name], ['class' => 'btn btn-primary']) ?>
        <?php
        echo Html::a(Yii::t('rbac-admin', 'Delete'), ['delete', 'id' => $model->name], [
            'class' => 'btn btn-danger',
            'data-confirm' => Yii::t('rbac-admin', 'Are you sure to delete this item?'),
            'data-method' => 'post',
        ]);
        ?>
    </p>

    <?php
    echo DetailView::widget([
        'model' => $model,

        'attributes' => [
            'name',
            'className',
        ],
    ]);
    ?>
</div>
</div>
