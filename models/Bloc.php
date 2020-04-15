<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-30 22:40:56
 * @Last Modified by:   Wang Chunsheng 2192138785@qq.com
 * @Last Modified time: 2020-04-14 09:55:01
 */


namespace diandi\admin\models;


use Yii;

/**
 * This is the model class for table "diandi_bloc".
 *
 * @property int $bloc_id
 * @property string $business_name 公司名称
 * @property int $pid 上级商户
 * @property string $category
 * @property string $province 省份
 * @property string $city 城市
 * @property string $district 区县
 * @property string $address 具体地址
 * @property string $longitude 经度
 * @property string $latitude 纬度
 * @property string $telephone 电话
 * @property string $photo_list
 * @property int $avg_price
 * @property string $recommend 介绍
 * @property string $special 特色
 * @property string $introduction 详细介绍
 * @property string $open_time 开业时间
 * @property int $location_id
 * @property int $status 1 审核通过 2 审核中 3审核未通过
 * @property int $source 1为系统门店，2为微信门店
 * @property string $message
 * @property string $sosomap_poi_uid 腾讯地图标注id
 * @property string $license_no 营业执照注册号或组织机构代码
 * @property string $license_name 营业执照名称
 * @property string $other_files 其他文件
 * @property string $audit_id 审核单id
 * @property int $on_show 微信后台设置的展示状态 1:展示；2:未展示
 */
class Bloc extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'diandi_bloc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['business_name', 'province', 'city', 'district', 'address', 'longitude', 'latitude', 'telephone', 'avg_price', 'recommend', 'special', 'introduction', 'open_time', 'status'], 'required'],
            [['pid', 'avg_price', 'status'], 'integer'],
            [['other_files'], 'string'],
            [['business_name', 'address', 'open_time', 'sosomap_poi_uid'], 'string', 'max' => 50],
            [['category', 'recommend', 'special', 'introduction'], 'string', 'max' => 255],
            [['province', 'city', 'district', 'longitude', 'latitude'], 'string', 'max' => 15],
            [['telephone'], 'string', 'max' => 20],
            [['license_no'], 'string', 'max' => 30],
            [['license_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'bloc_id' => '公司ID',
            'business_name' => '公司名称',
            'pid' => '上级商户',
            'category' => '分类',
            'province' => '省份',
            'city' => '城市',
            'district' => '区县',
            'address' => '具体地址',
            'longitude' => '经度',
            'latitude' => '纬度',
            'telephone' => '电话',
            'avg_price' => '平均消费',
            'recommend' => '介绍',
            'special' => '特色',
            'introduction' => '详细介绍',
            'open_time' => '开业时间',
            'status' => '审核状态',
            'sosomap_poi_uid' => '腾讯地图标注id',
            'license_no' => '营业执照注册号',
            'license_name' => '营业执照名称',
            'other_files' => '其他文件',
        ];
    }
}
