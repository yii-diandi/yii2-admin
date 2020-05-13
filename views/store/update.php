<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\BlocStore */

$this->title = 'Update Bloc Store: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Bloc Stores', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->store_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<?= $this->render('_tab') ?>


<div class="firetech-main">
    <div class="panel panel-default">
        <div class="box-body">
            <div class="bloc-store-update">


                <?= $this->render('_form', [
                'model' => $model,
                ]) ?>
            </div>
        </div>
    </div>
</div>