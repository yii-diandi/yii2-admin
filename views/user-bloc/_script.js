/*
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-06-27 10:01:37
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-06-27 11:07:57
 */

new Vue({
    el: '#user-bloc',
    data: {
        title: "打印标题",
        user_id:'',
        storelist:[],
        userlist:[],
        formLabelAlign:{},
        bloc_id:0,
        store_id:'',
        status:1,
        dialogStore:false,
        dialogUser:false,
    },
    created:function(){
        let that = this
        that.bloc_id = that.global.getUrlParam('bloc_id')
        console.log('创建开始',that.bloc_id)
        that.init();
    },
    methods: {
        init(){
            let that = this

            that.$http.post('getstore', {bloc_id:that.bloc_id},{
                headers:{
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                    'X-CSRF-Token':window.sysinfo.csrfToken // _csrf验证
                }
            }).then((response) => {
                console.log(response.data)
                 //响应成功回调
                if (response.data.code == 200) {
                    that.storelist = response.data.data.store
                    that.userlist = response.data.data.user
                }
            }, (response) => {
                //响应错误回调
                console.log(response)
            });
        },
        StoreDialog(){
            this.dialogStore= true
        },
        UserDialog(){
            this.dialogUser= true
            
        },
        submitForm(formName) {
            this.$refs[formName].validate((valid) => {
              if (valid) {
                alert('submit!');
              } else {
                console.log('error submit!!');
                return false;
              }
            });
        },
        resetForm(formName) {
        this.$refs[formName].resetFields();
        },
        selectStore(index, row) {
            console.log(index, row);
        },
        selectUser(index, row) {
        console.log(index, row);
        }
    }
});   