<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-09 06:36:33
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-09 06:49:21
 */
 

namespace diandi\admin\models\searchs;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use diandi\admin\models\UserGroup;

/**
 * UserGroupSearch represents the model behind the search form of `diandi\admin\models\UserGroup`.
 */
class UserGroupSearch extends UserGroup
{

    public $type;
    
    public $module_name='sys';

    public function __construct($item = null, $config = [])
    {
        if($item){
            $this->module_name = $item['module_name'];            
            $this->type = $this->module_name=='sys'?0:1;            
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type', 'created_at', 'updated_at'], 'integer'],
            [['name','module_name', 'description'], 'safe'],
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
        $query = UserGroup::find();
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'module_name' => $this->module_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);    
        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
