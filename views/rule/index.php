<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-10 16:03:47
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-10 16:12:36
 */
 

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this  yii\web\View */
/* @var $model diandi/admin\models\BizRule */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel diandi/admin\models\searchs\BizRule */

$this->title = Yii::t('rbac-admin', 'Rules');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="role-index">

<?=
    $this->render('_tab', [
        'model' => $model,
    ]);
    ?>
<div class="firetech-main">

    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{items}\n{pager}",

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'name',
                'label' => Yii::t('rbac-admin', 'Name'),
            ],
            ['class' => 'common\components\ActionColumn',],
        ],
    ]);
    ?>

</div>
</div>
