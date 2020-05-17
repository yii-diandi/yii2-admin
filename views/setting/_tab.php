<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-29 00:31:24
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-17 08:50:50
 */
use yii\helpers\Html;

$acname = Yii::$app->controller->action->id;
$bloc_id = Yii::$app->request->get('bloc_id', 1);

?>
<ul class="nav nav-tabs">
    <li <?php if ($acname == 'baidu') : ?> class="active" <?php endif; ?>>
        <?= Html::a('百度SDK参数', ['baidu', 'bloc_id' => $bloc_id], []); ?>
    </li>

    <li <?php if ($acname == 'wxapp') : ?> class="active" <?php endif; ?>>
        <?= Html::a('小程序设置', ['wxapp', 'bloc_id' => $bloc_id], []); ?>
    </li>
    <li <?php if ($acname == 'wechatpay') : ?> class="active" <?php endif; ?>>
        <?= Html::a('微信支付', ['wechatpay', 'bloc_id' => $bloc_id], []); ?>
    </li>
    <li <?php if ($acname == 'sms') : ?> class="active" <?php endif; ?>>
        <?= Html::a('短信设置', ['sms', 'bloc_id' => $bloc_id], []); ?>
    </li>
    <li <?php if ($acname == 'email') : ?> class="active" <?php endif; ?>>
        <?= Html::a('邮箱服务器', ['email', 'bloc_id' => $bloc_id], []); ?>
    </li>
    <li <?php if ($acname == 'map') : ?> class="active" <?php endif; ?>>
        <?= Html::a('地图设置', ['map', 'bloc_id' => $bloc_id], []); ?>
    </li>
</ul>