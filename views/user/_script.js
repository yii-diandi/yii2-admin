/*
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-05 20:52:48
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-03-26 11:22:24
 */

$('i.glyphicon-refresh-animate').hide();
function updateItems(r) {
    _opts.available = r.available;
    _opts.assigned = r.assigned;
    search('available');
    search('assigned');
}

$('.btn-assign').click(function () {
    var $this = $(this);
    var target = $this.data('target');

    var items = {
        group: [],
    }
    // 分组返回数据

    var items = $('select.list[data-target="' + target + '"]').val();

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
    console.log($(this).data('target'))
    search($(this).data('target'));
});

function search(target) {
    var $list = $('select.list[data-target="' + target + '"]');
    $list.html('');
    var q = $('.search[data-target="' + target + '"]').val();
    var groups = {
        modules : [$('<optgroup label="应用模块">'), false],
    };
    console.log(_opts,target,_opts[target])
    $.each(_opts[target], function (name, group) {
        $.each(group,function(index,item){
            console.log('667',item,item.title,q)
            if(item.title.indexOf(q)>=0){
                $('<option>').text(item.title).val(item.identifie).appendTo(groups[name][0]);
                groups[name][1] = true;
            }
            
        })
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
