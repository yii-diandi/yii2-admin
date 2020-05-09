/*
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-01 19:53:42
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-03 07:04:40
 */
$('#user_name').autocomplete({
    source: function (request, response) {
        var result = [];
        var limit = 10;
        var term = request.term.toLowerCase();
        $.each(_opts.menus, function () {
            var menu = this;
            if (term == '' || menu.name.toLowerCase().indexOf(term) >= 0 ||
                (menu.parent_name && menu.parent_name.toLowerCase().indexOf(term) >= 0) ||
                (menu.route && menu.route.toLowerCase().indexOf(term) >= 0)) {
                result.push(menu);
                limit--;
                if (limit <= 0) {
                    return false;
                }
            }
        });
        response(result);
    },
    focus: function (event, ui) {
        console.log(ui.item.name)
        $('#user_name').val(ui.item.name);
        return false;
    },
    select: function (event, ui) {
        $('#user_name').val(ui.item.name);
        $('#user_id').val(ui.item.id);
        return false;
    },
    search: function () {
        $('#user_id').val('');
    }
}).autocomplete("instance")._renderItem = function (ul, item) {
    console.log(ul)
    return $("<li>")
        .append($('<a>').append($('<b>').text(item.name)).append('<br>')
            .append($('<i>').text(item.parent_name + ' | ' + item.route)))
        .appendTo(ul);
};

$('#route').autocomplete({
    source: _opts.routes,
});