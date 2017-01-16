jQuery(document).ready(function ($) {

    function validate_credential_view() {
        var result = true;
        var user = $("#f2r_admin_connection_user").val(),
            pass = $("#f2r_admin_connection_pass").val(),
            host = $("#f2r_admin_connection_host").val(),
            db_name = $("#f2r_admin_connection_db_name").val();

        if (!user || !host || !db_name) {
            result = false;
        }

        return result;
    }

    $("input[name='f2r_submit']").click(function (e) {
        if (!validate_credential_view()) {
            e.preventDefault();
            alert(formidable2rdb.credential_invalid);
        }
    });

    $("#f2r_test_credential").click(function () {
        if (validate_credential_view()) {
            $(".f2r_loading").show();
            var user = $("#f2r_admin_connection_user").val(),
                pass = $("#f2r_admin_connection_pass").val(),
                host = $("#f2r_admin_connection_host").val(),
                db_name = $("#f2r_admin_connection_db_name").val();

            $.post(formidable2rdb.admin_url, {
                'action': 'test_credential',
                'user': user,
                'pass': pass,
                'host': host,
                'db_name': db_name,
                '_ajax_nonce': formidable2rdb.security
            }, function (data) {
                if (data) {
                    data = JSON.parse(data);
                    if (data.value == "test_credential") {
                        if (data.data == false) {
                            alert("Ok");
                        }
                        else {
                            alert(formidable2rdb.credential_fail);
                        }
                    }
                    else {
                        alert(formidable2rdb.general_error);
                    }
                }
                else {
                    alert(formidable2rdb.general_error);
                }
            }).fail(function () {
                alert(formidable2rdb.general_error);
            }).always(function () {
                $(".f2r_loading").hide();
            });
        }
        else {
            alert(formidable2rdb.credential_invalid);
        }
    });


    // function process_submit() {
    //     var $form = $(this);
    //     var $table_names = $('.f2r_table_name');
    //     var $table_name_are_empty = false;
    //     var $exist_in_another_actions = false;
    //     var $check_if_exist = false;
    //     var $is_valid = true;
    //     if ($table_names && $table_names.length) {
    //
    //         $table_names.each(function (i, object) {
    //             var new_name = $(this).val();
    //             var act_id = $(this).attr("action_id");
    //             var old_name = $("input[name='frm_formidable2rdb_action[" + act_id + "][post_content][f2r_old_table_name]']").val();
    //             var table_name_input = $("input[name='frm_formidable2rdb_action[" + act_id + "][post_content][f2r_table_name]']");
    //             if (!new_name) {
    //                 alert(formidable2rdb_action.table_name_required);
    //                 table_name_input.addClass("f2r_error");
    //                 move(table_name_input);
    //                 $is_valid = false;
    //                 return false;
    //             }
    //
    //             $table_names.each(function () {
    //                 if (($(this).attr("action_id") != act_id) && ($(this).val() == new_name)) {
    //                     $exist_in_another_actions = true;
    //                     $is_valid = false;
    //                     return false;
    //                 }
    //             });
    //
    //             if (!$exist_in_another_actions) {
    //                 if (old_name) {
    //                     if (old_name != new_name) {
    //                         $check_if_exist = true;
    //                         exist_table(act_id, new_name, $(this), $form);
    //                     }
    //                 }
    //                 else {
    //                     $check_if_exist = true;
    //                     exist_table(act_id, new_name, $(this), $form);
    //                 }
    //             }
    //             else {
    //                 alert(formidable2rdb_action.table_already_exist);
    //                 table_name_input.addClass("f2r_error");
    //                 move(table_name_input);
    //                 return false;
    //             }
    //         });
    //     }
    // }


    //
    // jQuery('.frm_submit_settings_btn').unbind("click").bind("click", function (e) {
    //     process_submit.call($("form.frm_form_settings"));
    // });
    //
    //
    // var setting_controller = false;
    // jQuery(document).bind('ajaxComplete ', function (event, xhr, settings) {
    //     if (settings.data.indexOf('frm_form_action_fill') != 0 && settings.data.indexOf('formidable2rdb') != 0) {
    //         if (setting_controller === true) {
    //             return;
    //         }
    //         setting_controller = false;
    //
    //         $(document).on('click', '.check_table', function (e) {
    //             var current_button = $(this);
    //             var action_id = current_button.attr("action_id");
    //             var table_name_input = $("input[name='frm_formidable2rdb_action[" + action_id + "][post_content][f2r_table_name]']");
    //             var table_name = table_name_input.val();
    //             if (table_name) {
    //                 var $exist_in_other_actions = false;
    //                 $('.f2r_table_name').each(function () {
    //                     if (($(this).attr("action_id") != action_id) && ($(this).val() == table_name)) {
    //                         $exist_in_other_actions = true;
    //                         return false;
    //                     }
    //                 });
    //                 if ($exist_in_other_actions) {
    //                     table_name_input.val("");
    //                     alert(formidable2rdb_action.table_already_exist);
    //                     table_name_input.addClass("f2r_error");
    //                     move(table_name_input);
    //                 }
    //                 else {
    //                     exist_table(action_id, table_name, table_name_input);
    //                 }
    //             }
    //             else {
    //                 alert(formidable2rdb_action.table_name_required);
    //                 table_name_input.addClass("f2r_error");
    //                 move(table_name_input);
    //             }
    //         });
    //
    //         //Only accept numbers in the length map
    //         $('.f2r_map_length').keypress(function (e) {
    //             var type = $(this).attr("field_type");
    //             var regex_str = "[0-9]";
    //             if (type == "number") {
    //                 regex_str = "[0-9-_]";
    //             }
    //             var regex = new RegExp("^" + regex_str + "+$");
    //             var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
    //             if (regex.test(str)) {
    //                 return true;
    //             }
    //
    //             e.preventDefault();
    //             return false;
    //         });
    //         //Only accept alpha in the name map
    //         $('.f2r_map_name').keypress(function (e) {
    //             var regex = new RegExp("^[a-zA-Z0-9-_]+$");
    //             var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
    //             if (regex.test(str)) {
    //                 return true;
    //             }
    //
    //             e.preventDefault();
    //             return false;
    //         });
    //         //validate the selected type for column
    //         //auto map
    //         if (formidable2rdb_action && formidable2rdb_action.f2r_auto_map) {
    //
    //         }
    //
    //         $(".f2r_map_type").change(function(e){
    //
    //         });
    //
    //     }
    // });

    var setting_controller = false;
    jQuery(document).bind('ajaxComplete ', function (event, xhr, settings) {
        var a = settings.data.indexOf('type=formidable2rdb');
        if (settings.data.indexOf('type=formidable2rdb') >= 0) {
            if (setting_controller == false) {





                // var submit_controller = false;
                // $(document).on('submit', '.frm_form_settings', function (e) {
                //     if (submit_controller === true) {
                //         submit_controller = false;
                //         return;
                //     }
                //     submit_controller = true;
                //     process_submit.call(this);
                // });
            }
            setting_controller = true;
        }
    });


});