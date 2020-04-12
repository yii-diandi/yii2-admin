<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-04-12 13:52:11
 * @Last Modified by:   Wang Chunsheng 2192138785@qq.com
 * @Last Modified time: 2020-04-12 18:32:24
 */

use yii\helpers\Html;

?>
<ul class="nav nav-tabs">
   
    <li class="active">
        <?= Html::a('管理员列表', ['index'], ['class' => 'btn btn-primary']) ?>
    </li>
    <li>
        <?= Html::a('添加管理员', ['signup'], ['class' => '']) ?>
    </li>
</ul>