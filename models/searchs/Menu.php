<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-27 16:49:41
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-06-13 18:17:17
 */

namespace diandi\admin\models\searchs;

use common\components\DataProvider\ArrayDataProvider;
use diandi\admin\models\Menu as MenuModel;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;


/**
 * Menu represents the model behind the search form about [[\diandi/admin\models\Menu]]
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 *
 * @since 1.0
 */
class Menu extends MenuModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'parent', 'order', 'level_type', 'is_show'], 'integer'],
            [['name', 'route', 'parent_name'], 'safe'],
            [['type', 'module_name'], 'string'],
            ['is_sys', 'in', 'range' => ['system', 'addons']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Searching menu.
     *
     * @param array $params
     *
     * @return \yii\data\ActiveDataProvider
     */
    public function search($params)
    {
        global $_GPC;

        $query = MenuModel::find()
            ->from(MenuModel::tableName().' t')
            ->joinWith(['menuParent' => function ($q) {
                $q->from(MenuModel::tableName().' parent');
            }]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $sort = $dataProvider->getSort();
        $sort->attributes['menuParent.name'] = [
            'asc' => ['parent.name' => SORT_ASC],
            'desc' => ['parent.name' => SORT_DESC],
            'label' => 'parent',
        ];
        $sort->attributes['order'] = [
            'asc' => ['parent.order' => SORT_ASC, 't.order' => SORT_ASC],
            'desc' => ['parent.order' => SORT_DESC, 't.order' => SORT_DESC],
            'label' => 'order',
        ];
        $sort->defaultOrder = ['menuParent.name' => SORT_ASC];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return false;
        }

        $query->andFilterWhere([
            't.id' => $this->id,
            't.module_name' => $this->module_name,
            't.parent' => $this->parent,
        ]);

        $query->andFilterWhere(['like', 'lower(t.name)', strtolower($this->name)])
            ->andFilterWhere(['like', 't.route', $this->route])
            ->andFilterWhere(['like', 'lower(parent.name)', strtolower($this->parent_name)])->orderBy('order');

        $count = $query->count();
        $pageSize = $_GPC['pageSize'];
        $page = $_GPC['page'];
        // 使用总数来创建一个分页对象
        $pagination = new Pagination([
            'totalCount' => $count,
            'pageSize' => $pageSize,
            'page' => $page - 1,
            // 'pageParam'=>'page'
        ]);

        $list = $query->offset($pagination->offset)
            // ->limit($pagination->limit)
            ->asArray()
            ->all();

        //foreach ($list as $key => &$value) {
        //    $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
        //    $value['update_time'] = date('Y-m-d H:i:s',$value['update_time']);
        //}

        $provider = new ArrayDataProvider([
            'key' => 'id',
            'allModels' => $list,
            'totalCount' => isset($count) ? $count : 0,
            'total' => isset($count) ? $count : 0,
            'sort' => [
                'attributes' => [
                    //'member_id',
                ],
                'defaultOrder' => [
                    //'member_id' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        return $provider;
    }
}
