jQuery(document).ready(function ($) {

    function move($element) {
        $('html, body').animate({
            scrollTop: $element.offset().top - 150
        }, 500);
    }

    function f2r_exist_table(action_id, table_name, table_name_input, form) {
        $("#f2r_loading_" + action_id).show();
        $.post(formidable2rdb.admin_url, {
            'action': 'get_add_columns',
            'table_name': table_name,
            '_ajax_nonce': formidable2rdb.security
        }, function (data) {
            if (data) {
                data = JSON.parse(data);
                if (data.value == "exist_table") {
                    if (data.data == false) {
                        if (typeof form == "undefined") {
                            $("#table_structure_" + action_id).show();
                            $("#table_name_result_" + action_id).text(table_name);
                        }
                        else {
                            form.submit();
                        }
                    }
                    else {
                        table_name_input.addClass("f2r_error");
                        move(table_name_input);
                        alert(formidable2rdb.table_already_exist);
                    }
                }
                else {
                    alert("1-" + formidable2rdb.general_error);
                }
            }
            else {
                alert("2-" + formidable2rdb.general_error);
            }
        }).fail(function () {
            if (typeof form == "undefined") {
                table_name_input.val("");
            }
            alert("3-" + formidable2rdb.general_error);
        }).always(function () {
            $("#f2r_loading_" + action_id).hide();
        });
    }


    var setting_controller = false;
    var a = 0;
    jQuery(document).bind('ajaxComplete ', function (event, xhr, settings) {
        var a = settings.data.indexOf('type=formidable2rdb');
        if (settings.data.indexOf('type=formidable2rdb') >= 0) {
            // if (setting_controller == false) {

            //Only accept numbers in the length and the precision map
            $('.f2r_map_length,.f2r_map_precision').keypress(function (e) {
                var type = $(this).attr("field_type");
                var regex_str = "[0-9]";
                if (type == "number") {
                    regex_str = "[0-9-_]";
                }
                var regex = new RegExp("^" + regex_str + "+$");
                var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
                if (regex.test(str)) {
                    return true;
                }

                e.preventDefault();
                return false;
            });
            //Only accept alpha in the name map
            $('.f2r_map_name').keypress(function (e) {
                var regex = new RegExp("^[a-zA-Z0-9-_]+$");
                var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
                if (regex.test(str)) {
                    return true;
                }

                e.preventDefault();
                return false;
            });

            $(".check_table").click(function (e) {
                var current_button = $(this);
                var action_id = current_button.attr("action_id");
                var table_name_input = $("input[name='frm_formidable2rdb_action[" + action_id + "][post_content][f2r_table_name]']");
                var table_name = table_name_input.val();
                table_name_input.removeClass("f2r_error");
                if (table_name) {
                    var $exist_in_other_actions = false;
                    $('.f2r_table_name').each(function () {
                        if (($(this).attr("action_id") != action_id) && ($(this).val() == table_name)) {
                            $exist_in_other_actions = true;
                            return false;
                        }
                    });
                    if ($exist_in_other_actions) {
                        alert(formidable2rdb.table_already_exist);
                        table_name_input.addClass("f2r_error");
                        move(table_name_input);
                    }
                    else {
                        f2r_exist_table(action_id, table_name, table_name_input);
                    }
                }
                else {
                    alert(formidable2rdb.table_name_required);
                    table_name_input.addClass("f2r_error");
                    move(table_name_input);
                }
            });


            // var submit_controller = false;
            // $(document).on('submit', '.frm_form_settings', function (e) {
            //     if (submit_controller === true) {
            //         submit_controller = false;
            //         return;
            //     }
            //     submit_controller = true;
            //     process_submit.call(this);
            // });

            setting_controller = true;
        }
    });

});