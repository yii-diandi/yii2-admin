<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2022-06-18 10:25:14
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-08-01 18:04:10
 */

namespace diandi\admin;

use yii\web\AssetBundle;

/**
 * Description of AnimateAsset.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 *
 * @since 2.5
 */
class AnimateAsset extends AssetBundle
{
    /**
     * {@inheritdoc}
     */
    public $sourcePath = '@diandi/admin/assets';
    /**
     * {@inheritdoc}
     */
    public $css = [
        'animate.css',
    ];
}
