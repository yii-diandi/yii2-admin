<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-14 01:25:51
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-08-03 10:52:11
 */

namespace diandi\admin\models\form;

use diandi\admin\models\BlocConfWechatpay;
use yii\base\Model;

class Wechatpay extends Model
{
    /**
     * @var string application name
     */
    public $appId;
    public $id;

    public $bloc_id;
    /**
     * @var string admin email
     */
    public $mch_id;
    public $app_id;
    public $key;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [[
                'mch_id',
                'app_id',
                'key',
            ], 'string'],
            [['id', 'bloc_id'], 'integer'],
        ];
    }

    public function getConf($bloc_id)
    {
        $conf = new BlocConfWechatpay();
        $bloc = $conf::find()->where(['bloc_id' => $bloc_id])->asArray()->one();

        $this->id = $bloc['id'];
        $this->bloc_id = $bloc['bloc_id'];
        $this->mch_id = $bloc['mch_id'];
        $this->app_id = $bloc['app_id'];
        $this->key = $bloc['key'];
    }

    public function saveConf($bloc_id)
    {
        if (!$this->validate()) {
            return null;
        }
        $conf = BlocConfWechatpay::findOne(['bloc_id' => $bloc_id]);

        if (!$conf) {
            $conf = new BlocConfWechatpay();
        }

        $conf->bloc_id = $bloc_id;
        $conf->mch_id = $this->mch_id;
        $conf->app_id = $this->app_id;
        $conf->key = $this->key;

        return $conf->save();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'mch_id' => '支付商户号',
            'app_id' => 'AppId',
            'key' => '秘钥',
        ];
    }
}
