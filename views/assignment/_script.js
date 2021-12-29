/*
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-06 15:28:38
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-12-29 00:19:04
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
    // var items = $('select.list[data-target="' + target + '"]').val();

    var items = {
        role: [],
        permission: []
    }
    // 分组返回数据
    var opt = $('select.list[data-target="' + target + '"]').find(':selected');
    $.each(opt,function(key,name){
        var og = $(this).closest('optgroup').attr('label')
        if(og=='用户组'){
            items.role.push($(this).val())
        }
        if(og=='权限'){
            items.permission.push($(this).val())
        }
    })
    
    if (items) {
        $this.children('i.glyphicon-refresh-animate').show();
        $.post($this.attr('href'), {items: items}, function (r) {
            updateItems(r);
            location.reload()
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
        role: [$('<optgroup label="用户组">'), false],
        permission: [$('<optgroup label="权限">'), false],
    };
    $.each(_opts.items[target], function (name, group) {
        if (name.indexOf(q) >= 0 && group != 'route') {
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
