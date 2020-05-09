/*
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-05 20:52:48
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-08 23:39:48
 */



$('#rule_name').autocomplete({
    source: _rule,
});
$('#parent_name').autocomplete({
    source:function (request, response) {
        console.log(_item)
        result = [];
        $.each(_item, function (index, value) { 
            console.log(value)
             var item = {'label':value.name,'value':value.id};
             result.push(item)
        }); 
        response(result);        
    },
    select: function (event, ui) {
        console.log(ui)
        $('#parent_name').val(ui.item.label);
        $('#parent_id').val(ui.item.value);
        return false;
    },
});