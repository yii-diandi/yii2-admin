<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-11-19 00:24:21
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-12-08 19:31:25
 */
 

namespace diandi\admin\models\searchs;

use yii\base\Model;
use common\components\DataProvider\ArrayDataProvider;
use diandi\admin\models\StoreCategory as StoreCategoryModel;
use yii\data\Pagination;


/**
 * StoreCategory represents the model behind the search form of `diandi\admin\models\StoreCategory`.
 */
class StoreCategory extends StoreCategoryModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'parent_id', 'sort', 'create_time', 'update_time'], 'integer'],
            [['name', 'thumb'], 'safe'],
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider

     */
    public function search($params)
    {
        global $_GPC;
        $query = StoreCategoryModel::find();

        

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return false;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'category_id' => $this->category_id,
            'parent_id' => $this->parent_id,
            'sort' => $this->sort,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'thumb', $this->thumb]);
        
    

        $list = $query
            ->asArray()
            ->all();
        
        //foreach ($list as $key => &$value) {
        //    $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
        //    $value['update_time'] = date('Y-m-d H:i:s',$value['update_time']);
        //} 
            

        $provider = new ArrayDataProvider([
            'key'=>'category_id',
            'allModels' => $list,
            'totalCount' => isset($count) ? $count : 0,
            'total'=> isset($count) ? $count : 0,
            'sort' => [
                'attributes' => [
                    //'member_id',
                ],
                'defaultOrder' => [
                    //'member_id' => SORT_DESC,
                ],
            ],
            'pagination' => false
        ]);
        
        return $provider;
        
    }
}