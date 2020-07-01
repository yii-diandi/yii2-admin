<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-01 19:13:36
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-06-27 11:10:31
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model diandi\admin\models\UserBloc */
/* @var $form yii\widgets\MyActiveForm */

?>

<div class="user-bloc-form">

    <el-form label-position="top" label-width="80px" :model="formLabelAlign">
        <el-form-item label="管理员">
            <el-input placeholder="管理员" :disabled="true" v-model="user_id">
                <el-button slot="append"   @click="UserDialog">选择管理员</el-button>
            </el-input>
        </el-form-item>
        <el-form-item label="商户">
            <el-input placeholder="商户" :disabled="true" v-model="store_id">
                <el-button slot="append"   @click="StoreDialog">选择商户</el-button>
            </el-input>
        </el-form-item>
        <el-form-item label="是否审核">
            <template>
                <el-radio v-model="status" label="1">审核通过</el-radio>
                <el-radio v-model="status" label="0">待审核</el-radio>
            </template>
        </el-form-item>
        <el-form-item>
            <el-button type="primary" @click="submitForm('ruleForm')">立即创建</el-button>
            <el-button @click="resetForm('ruleForm')">重置</el-button>
        </el-form-item>
    </el-form>
    

</div>

<el-button type="text" @click="dialogTableVisible = true">打开嵌套表格的 Dialog</el-button>

<el-dialog title="选择管理员" :visible.sync="dialogUser">
  <el-table :data="userlist">
    <el-table-column property="avatar" label="头像" >
            <template slot-scope="scope">
                <el-image
                style="width: 50px; height: 50px"
                :src="scope.row.avatar"
                ></el-image>
            </template>
        
        </el-table-column>
        <el-table-column property="username" label="用户名" ></el-table-column>
        <el-table-column label="操作">
            <template slot-scope="scope">
                <el-button
                size="mini"
                @click="selectUser(scope.$index, scope.row)">选择</el-button>
            </template>
        </el-table-column>
  </el-table>
</el-dialog>

<el-dialog title="选择商户" :visible.sync="dialogStore" style="width: 85%">
  <el-table :data="storelist">
    <el-table-column property="logo" label="LOGO" width="150">
        <template slot-scope="scope">
            <el-image
            style="width: 50px; height: 50px"
            :src="scope.row.logo"
            ></el-image>
        </template>
       
    </el-table-column>
    <el-table-column property="name" label="商户名称" ></el-table-column>
    <el-table-column label="操作">
      <template slot-scope="scope">
        <el-button
          size="mini"
          @click="selectStore(scope.$index, scope.row)">选择</el-button>
      </template>
    </el-table-column>
  </el-table>
</el-dialog>
