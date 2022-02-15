<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-09 15:23:37
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-02-14 13:31:39
 */

namespace diandi\admin\models\searchs;

use diandi\admin\models\AddonsUser;
use diandi\admin\models\User as UserModel;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * User represents the model behind the search form of `diandi/admin\models\User`.
 */
class User extends UserModel
{
    public $module_name;

    public $user_ids;

    /**
     * {@inheritdoc}
     */
    public function __construct($item, $config = [])
    {
        $this->module_name = Yii::$app->request->get('module_name', 'sys');
        if (isset($item['user_ids'])) {
            $this->user_ids = $item['user_ids'] ? $item['user_ids'] : [0];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'auth_key', 'password_hash', 'password_reset_token', 'email', 'verification_token', 'avatar', 'mobile',
            'company', ], 'safe'],
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
            'id' => $this->user_ids,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // 'addonsUser.module_name'=>$this->module_name
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'verification_token', $this->verification_token])
            ->andFilterWhere(['like', 'avatar', $this->avatar]);

        return $dataProvider;
    }
}
