<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2021-04-27 11:40:52
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-05-19 16:56:39
 */
 

namespace diandi\admin\models\searchs;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use diandi\admin\models\BizRule as MBizRule;
use diandi\admin\components\RouteRule;
use diandi\admin\components\Configs;

/**
 * Description of BizRule
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class BizRule extends Model
{
    /**
     * @var string name of the rule
     */
    public $name;

    public function rules()
    {
        return [
            [['name'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('rbac-admin', 'Name'),
        ];
    }

    /**
     * Search BizRule
     * @param array $params
     * @return \yii\data\ActiveDataProvider|\yii\data\ArrayDataProvider
     */
    public function search($params)
    {
        /* @var \yii\rbac\Manager $authManager */
        $authManager = Configs::authManager();
        $models = [];
        $included = !($this->load($params) && $this->validate() && trim($this->name) !== '');
        foreach ($authManager->getRules() as $name => $item) {
            if ($name != RouteRule::RULE_NAME && ($included || stripos($item->name, $this->name) !== false)) {
                $models[$name] = new MBizRule($item);
            }
        }

        return new ArrayDataProvider([
            'allModels' => $models,
        ]);
    }
}
