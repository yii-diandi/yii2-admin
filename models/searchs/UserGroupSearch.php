<?php

/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-09 06:36:33
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-10-28 18:01:46
 */

namespace diandi\admin\models\searchs;

use admin\services\UserService;
use common\components\DataProvider\ArrayDataProvider;
use diandi\admin\models\UserGroup;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;

/**
 * UserGroupSearch represents the model behind the search form of `diandi\admin\models\UserGroup`.
 */
class UserGroupSearch extends UserGroup
{

    public $is_sys;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'store_id', 'bloc_id', 'is_sys'], 'integer'],
            [['name', 'description'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied.
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {

        $query = UserGroup::find();
        // add conditions that should always apply here

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return false;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'store_id' => $this->store_id,
            'bloc_id' => $this->bloc_id
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description]);
        if (isset($this->is_sys)) {
            $query->andFilterWhere(['is_sys' => $this->is_sys]);
            $isbusinessRoles = UserService::isbusinessRoles();
            $isSuperAdmin = UserService::isSuperAdmin();

            if ($isbusinessRoles === 0 && !$isSuperAdmin === 0 && (int)$this->is_sys === 0) {
                $bloc_id = \Yii::$app->request->headers['bloc-id'];
                $query->andWhere(['bloc_id' => (int)$bloc_id]);
            }
        }
        $count = $query->count();
        $pageSize = \Yii::$app->request->input('pageSize', 10);
        $page = \Yii::$app->request->input('page', 1);
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
