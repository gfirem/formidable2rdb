function FormidableFnc() {
    var controller = false;

    function saveValues(e) {
        jQuery('.frm_single_formidable2rdb_settings ').each(function() {
            var actionId = jQuery(this).attr('data-actionkey');
            if (actionId) {
                var action_is_loaded = jQuery('[name="frm_formidable2rdb_action[' + actionId + '][post_content][f2r_mapped_field]"]').length > 0;
                if (action_is_loaded) {
                    var mapped_field = jQuery("[name='frm_formidable2rdb_action[" + actionId + "][post_content][f2r_mapped_field]']");
                    var mapped_old_field = jQuery("[name='frm_formidable2rdb_action[" + actionId + "][post_content][f2r_old_mapped_field]']");
                    var mapped_table_name = jQuery("[name='frm_formidable2rdb_action[" + actionId + "][post_content][f2r_table_name]']");
                    var mapped_old_table_name = jQuery("[name='frm_formidable2rdb_action[" + actionId + "][post_content][f2r_old_table_name]']");

                    var action_fields = jQuery('table.f2r_map_table tr.f2r_row').map(function(i, v) {
                        var $id = jQuery('.f2r_map_id', this);
                        var $enabled = jQuery('.f2r_map_enabled', this);
                        var $name = jQuery('.f2r_map_name', this);
                        var $default = jQuery('.f2r_map_default', this);
                        var $type = jQuery('.f2r_map_type', this);
                        var $length = jQuery('.f2r_map_length ', this);
                        var $precision = jQuery('.f2r_map_precision', this);
                        var $null = jQuery('.f2r_map_not_null', this);
                        return {
                            'Id': $id.val(),
                            'Enabled': $enabled.is(":checked"),
                            'Field': $name.val(),
                            'Default': $default.val(),
                            'Type': $type.val(),
                            'Length': $length.val(),
                            'Precision': $precision.val(),
                            'Null': $null.val()
                        }
                    }).get();

                    var json = JSON.stringify(action_fields);
                    if (mapped_field) {
                        mapped_field.val(json);
                        if (!mapped_old_field.val()) {
                            mapped_old_field.val(json);
                        }

                        if (!mapped_old_table_name.val()) {
                            mapped_old_table_name.val(mapped_table_name.val());
                        }

                        if (!mapped_table_name.val()) {
                            e.preventDefault();
                            mapped_table_name.addClass("f2r_error");
                            alert(formidable2rdb.table_name_required);
                        } else {
                            mapped_table_name.removeClass("f2r_error");
                        }
                    }
                }
            }
        });
    }

    function move($element) {
        jQuery('html, body').animate({
            scrollTop: $element.offset().top - 150
        }, 500);
    }

    function f2r_exist_table(action_id, table_name, table_name_input, form) {
        jQuery("#f2r_loading_" + action_id).show();
        jQuery.post(formidable2rdb.admin_url, {
            'action': 'get_add_columns',
            'table_name': table_name,
            '_ajax_nonce': formidable2rdb.security
        }, function(data) {
            if (data) {
                data = JSON.parse(data);
                if (data.value == "exist_table") {
                    if (data.data == false) {
                        if (typeof form == "undefined") {
                            jQuery("#table_structure_" + action_id).show();
                            jQuery("#table_name_result_" + action_id).text(table_name);
                        }
                        else {
                            form.submit();
                        }
                    } else {
                        table_name_input.addClass("f2r_error");
                        move(table_name_input);
                        alert(formidable2rdb.table_already_exist);
                    }
                } else {
                    alert("1-" + formidable2rdb.general_error);
                }
            } else {
                alert("2-" + formidable2rdb.general_error);
            }
        }).fail(function() {
            if (typeof form == "undefined") {
                table_name_input.val("");
            }
            alert("3-" + formidable2rdb.general_error);
        }).always(function() {
            jQuery("#f2r_loading_" + action_id).hide();
        });
    }

    /**
     * Validate the default base on the type of the column will map
     * @param current Selected Type of column
     * @param group
     */
    function process_default_validation(current, group) {
        var field_id = current.attr("field_id"),
            form_type = current.attr("field_type"),
            default_input = jQuery("input[name='f2r_column_default_" + field_id + "']");
        var regex_str = false;
        if (group == "number") {
            regex_str = "[0-9]";
        } else if (group == "text") {
            regex_str = "[a-zA-Z0-9-_]";
        }

        if (regex_str !== false) {
            default_input.unbind("keypress");
            bind_validation(default_input, regex_str);
        }
    }

    function bind_validation($element, $regex) {
        $element.keypress(function(e) {
            var regex = new RegExp("^" + $regex + "+$");
            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            if (regex.test(str)) {
                return true;
            }

            e.preventDefault();
            return false;
        });
    }

    function process_types(current, action, force_default) {
        var field_id = current.attr("field_id"),
            form_type = current.attr("field_type"),
            col_type = current.val();

        if (form_type && field_id && col_type && formidable2rdb && formidable2rdb.map_column && formidable2rdb.map_column[form_type]) {
            jQuery.each(formidable2rdb.map_column[form_type], function(index, value) {
                var rule = formidable2rdb.map_column[form_type][index];
                if (rule["type"] == col_type) {
                    process_default_validation(current, rule["group"]);
                    var precision_input = action.find("input[name='f2r_column_precision_" + field_id + "']"),
                        default_input = action.find("input[name='f2r_column_default_" + field_id + "']"),
                        length_input = action.find("input[name='f2r_column_length_" + field_id + "']");

                    if (rule["need_default"]) {
                        default_input.removeAttr("disabled");
                    } else {
                        default_input.attr("disabled", "disabled");
                    }
                    if (rule["need_length"]) {
                        length_input.removeAttr("disabled");
                    } else {
                        length_input.attr("disabled", "disabled");
                    }
                    if (rule["need_precision"]) {
                        precision_input.removeAttr("disabled");
                    } else {
                        precision_input.attr("disabled", "disabled");
                    }
                    if (force_default) {
                        default_input.attr("maxlength", rule["default_length"]);
                        length_input.val(rule["default_length"]);
                        precision_input.val(rule["default_precision"]);
                    } else {
                        if (!length_input.val()) {
                            length_input.val(rule["default_length"]);
                            default_input.attr("maxlength", rule["default_length"]);
                        } else {
                            default_input.attr("maxlength", length_input.val());
                        }
                        if (!precision_input.val()) {
                            precision_input.val(rule["default_precision"]);
                        }
                    }
                }
            });
        }
    }

    function isTableNameAvailable() {
        var current_button = jQuery(this);
        var action_id = current_button.attr("action_id");
        var table_name_input = jQuery("input[name='frm_formidable2rdb_action[" + action_id + "][post_content][f2r_table_name]']");
        var table_name = table_name_input.val();
        table_name_input.removeClass("f2r_error");
        if (table_name) {
            var $exist_in_other_actions = false;
            jQuery('.f2r_table_name').each(function() {
                if ((jQuery(this).attr("action_id") != action_id) && (jQuery(this).val() == table_name)) {
                    $exist_in_other_actions = true;
                    return false;
                }
            });
            if ($exist_in_other_actions) {
                alert(formidable2rdb.table_already_exist);
                table_name_input.addClass("f2r_error");
                move(table_name_input);
            } else {
                f2r_exist_table(action_id, table_name, table_name_input);
            }
        } else {
            alert(formidable2rdb.table_name_required);
            table_name_input.addClass("f2r_error");
            move(table_name_input);
        }
    }

    return {
        init: function() {
            if (document.getElementById('frm_notification_settings') !== null) {
                frmFormidable2Rdb.actionInit();
            }
        },
        actionInit: function() {
            var $formActions = jQuery(document.getElementById('frm_notification_settings')),
                $form = jQuery(document.getElementsByClassName('frm_form_settings'));
            $form.on('submit', saveValues);
            jQuery(document).bind('ajaxComplete ', function(event, xhr, settings) {
                if (settings.data) {
                    if (settings.data.indexOf('frm_form_action_fill') !== 0 && settings.data.indexOf('formidable2rdb') !== 0) {

                        jQuery('.frm_single_formidable2rdb_settings ').each(function() {
                            var action = jQuery(this);

                            var select_types = jQuery(".f2r_map_type");

                            jQuery.each(select_types, function() {
                                process_types(jQuery(this), action);
                            });

                            select_types.change(function() {
                                process_types(jQuery(this), action);
                            });

                            var needsLoad = action.attr('is-ready');
                            needsLoad = (typeof(needsLoad) === 'undefined');
                            if (needsLoad) {
                                if (controller === false) {
                                    $formActions.on('click', '.check_table', isTableNameAvailable);
                                    controller = true;
                                }


                                jQuery(".f2r_map_length").change(function() {
                                    var field_id = jQuery(this).attr("field_id"),
                                        default_input = action.find("input[name='f2r_column_default_" + field_id + "']");
                                    if (default_input.val().length > jQuery(this).val()) {
                                        default_input.val("");
                                    }
                                    default_input.attr("maxlength", jQuery(this).val());
                                });

                                jQuery(".f2r_map_enabled").click(function() {
                                    var field_id = jQuery(this).attr("field_id"),
                                        help_tooltip = action.find("#frm_help_column_enabled_" + field_id);
                                    if (!jQuery(this).is(':checked')) {
                                        help_tooltip.show();
                                    } else {
                                        help_tooltip.hide();
                                    }
                                });

                                jQuery(".f2r_show_repeatable_fields").click(function() {
                                    var field_id = jQuery(this).attr("field_id"),
                                        section = action.find('#f2r_hidden_repeatable_section_' + field_id);
                                    if (!section.is(':visible')) {
                                        section.show();
                                    } else {
                                        section.hide();
                                    }
                                });

                                //Only accept numbers in the length and the precision map
                                bind_validation(jQuery('.f2r_map_length,.f2r_map_precision'), "[0-9]");

                                //Only accept alpha in the name map
                                bind_validation(jQuery('.f2r_map_name,.f2r_table_name'), "[a-z0-9-_]");

                                action.attr('is-ready', 'true');
                            }
                        });

                    }
                }
            });
        }
    }
}

var frmFormidable2Rdb = FormidableFnc();
jQuery(document).ready(function() {
    frmFormidable2Rdb.init();
});
