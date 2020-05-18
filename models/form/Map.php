<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-04-30 17:04:04
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-17 08:47:49
 */

namespace diandi\admin\models\form;

use common\helpers\ErrorsHelper;
use diandi\admin\models\BlocConfMap;
use yii\base\Model;

class Map extends Model
{
    /**
     * @var string application name
     */
    public $id;

    public $bloc_id;
    /**
     * @var string admin email
     */
    public $baiduApk;
    public $amapApk;
    public $tencentApk;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [[
                'baiduApk',
                'amapApk',
                'tencentApk',
            ], 'string'],
            [['id', 'bloc_id'], 'integer'],
        ];
    }

    public function getConf($bloc_id)
    {
        $conf = new BlocConfMap();
        $bloc = $conf::findOne(['bloc_id' => $bloc_id]);
        $this->id = $bloc->id;
        $this->bloc_id = $bloc->bloc_id;
        $this->baiduApk = $bloc->baiduApk;
        $this->amapApk = $bloc->amapApk;
        $this->tencentApk = $bloc->tencentApk;
    }

    public function saveConf($bloc_id)
    {
        if (!$this->validate()) {
            return null;
        }

        $conf = BlocConfMap::findOne(['bloc_id' => $bloc_id]);
        if (!$conf) {
            $conf = new BlocConfMap();
        }
        $conf->bloc_id = $bloc_id;

        $conf->baiduApk = $this->baiduApk;
        $conf->amapApk = $this->amapApk;
        $conf->tencentApk = $this->tencentApk;
        $conf->save();

        return $conf->save();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'baiduApk' => '百度地图APK',
            'amapApk' => '高德地图APK',
            'tencentApk' => '腾讯地图APK',
        ];
    }
}