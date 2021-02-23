/*
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-05 20:52:48
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-02-23 21:00:42
 */

$('i.glyphicon-refresh-animate').hide();
function updateItems(r) {
    _opts.items.available = r.available;
    _opts.items.assigned = r.assigned;
    search('available');
    search('assigned');
}

$('.btn-assign').click(function () {
    var $this = $(this);
    var target = $this.data('target');

    var items = {
        role: [],
        permission: [],
        route: [],
    }
    // 分组返回数据
    var opt = $('select.list[data-target="' + target + '"]').find(':selected');
    console.log(opt);
    
    $.each(opt,function(key,name){
        var og = $(this).closest('optgroup').attr('label')
        if(og=='Roles'){
            items.role.push($(this).val())
        }
        if(og=='权限'){
            items.permission.push($(this).val())
        }
        if(og=='路由'){
            items.route.push($(this).val())
        }
    })
    

    // var items = $('select.list[data-target="' + target + '"]').val();

    if (items) {
        $this.children('i.glyphicon-refresh-animate').show();
        $.post($this.attr('href'), {items: items}, function (r) {
            updateItems(r);
        }).always(function () {
            $this.children('i.glyphicon-refresh-animate').hide();
        });
    }
    return false;
});

$('.search[data-target]').keyup(function () {
    search($(this).data('target'));
});

function search(target) {
    var $list = $('select.list[data-target="' + target + '"]');
    $list.html('');
    var q = $('.search[data-target="' + target + '"]').val();

    var groups = {
        role: [$('<optgroup label="Roles">'), false],
        permission: [$('<optgroup label="权限">'), false],
        route: [$('<optgroup label="路由">'), false],
    };
    console.log(_opts.items)
    $.each(_opts.items[target], function (name, group) {
        if (name.indexOf(q) >= 0) {
            $('<option>').text(name).val(name).appendTo(groups[group][0]);
            groups[group][1] = true;
        }
    });
    $.each(groups, function () {
        if (this[1]) {
            $list.append(this[0]);
        }
    });
}

// initial
search('available');
search('assigned');
