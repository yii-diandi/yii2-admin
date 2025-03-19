<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-09 15:23:37
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-10-28 19:46:34
 */

namespace diandi\admin\models\searchs;

use diandi\admin\models\User as UserModel;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * User represents the model behind the search form of `diandi/admin\models\User`.
 */
class User extends UserModel
{
    private $group_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'updated_at','department_id','is_super_admin'], 'integer'],
            [['username', 'email', 'mobile'], 'safe'],
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
     * Creates data provider instance with search query applied.
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = UserModel::find();

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
            'status' => $this->status,
            'department_id'=>$this->department_id,
            'id' => $this->user_ids,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'UserGroup.group_id' => $this->group_id,
        ]);

        if (isset($this->is_super_admin)){
            $query->andFilterWhere(['is_super_admin' => $this->is_super_admin]);
        }

        $query->andFilterWhere(['like', 'username', $this->username]);

        return $dataProvider;
    }
}
