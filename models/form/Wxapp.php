<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-04-30 17:04:04
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-04-30 22:40:29
 */

namespace diandi\admin\models\form;

use common\helpers\ErrorsHelper;
use diandi\admin\models\BlocConfWxapp;
use yii\base\Model;

class Wxapp extends Model
{
    /**
     * @var string application name
     */
    public $name;
    public $id;

    public $bloc_id;
    /**
     * @var string admin email
     */
    public $description;
    public $original;
    public $AppId;
    public $AppSecret;
    public $headimg;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [[
                'name',
                'description',
                'original',
                'AppId',
                'AppSecret',
                'headimg',
            ], 'string'],
            [['id', 'bloc_id'], 'integer'],
        ];
    }

    public function getConf($bloc_id)
    {
        $conf = new BlocConfWxapp();
        $bloc = $conf::findOne(['bloc_id' => $bloc_id]);
        $this->id = $bloc->id;
        $this->bloc_id = $bloc->bloc_id;

        $this->name = $bloc->name;
        $this->description = $bloc->description;
        $this->original = $bloc->original;
        $this->AppId = $bloc->AppId;
        $this->AppSecret = $bloc->AppSecret;
        $this->headimg = $bloc->headimg;
    }

    public function saveConf($bloc_id)
    {
        if (!$this->validate()) {
            return null;
        }

        $conf = BlocConfWxapp::findOne(['bloc_id' => $bloc_id]);
        if (!$conf) {
            $conf = new BlocConfWxapp();
        }
        $conf->bloc_id = $bloc_id;
        $conf->name = $this->name;
        $conf->description = $this->description;
        $conf->original = $this->original;
        $conf->AppId = $this->AppId;
        $conf->AppSecret = $this->AppSecret;
        $conf->headimg = $this->headimg;
        $conf->save();
        print_r(ErrorsHelper::getModelError($conf));

        return $conf->save();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
           'name' => '小程序名称',
            'description' => '小程序描述',
            'original' => '原始id',
            'AppId' => 'AppId',
            'AppSecret' => 'AppSecret',
            'headimg' => '二维码',
        ];
    }
}
