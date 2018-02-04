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
		}).fail(function () {
			if (typeof form == "undefined") {
				table_name_input.val("");
			}
			alert("3-" + formidable2rdb.general_error);
		}).always(function () {
			$("#f2r_loading_" + action_id).hide();
		});
	}

	/**
	 * Validate the default base on the type of the column will map
	 * @param $ Context
	 * @param current Selected Type of column
	 */
	function process_default_validation($, current, group) {
		var field_id = current.attr("field_id"),
			form_type = current.attr("field_type"),
			default_input = $("input[name='f2r_column_default_" + field_id + "']");
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
		$element.keypress(function (e) {
			var regex = new RegExp("^" + $regex + "+$");
			var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if (regex.test(str)) {
				return true;
			}

			e.preventDefault();
			return false;
		});
	}

	function process_types($, current, force_default) {
		var field_id = current.attr("field_id"),
			form_type = current.attr("field_type"),
			col_type = current.val();

		if (form_type && field_id && col_type && formidable2rdb && formidable2rdb.map_column && formidable2rdb.map_column[form_type]) {
			jQuery.each(formidable2rdb.map_column[form_type], function (index, value) {
				var rule = formidable2rdb.map_column[form_type][index];
				if (rule["type"] == col_type) {
					process_default_validation($, current, rule["group"]);
					var precision_input = $("input[name='f2r_column_precision_" + field_id + "']"),
						default_input = $("input[name='f2r_column_default_" + field_id + "']"),
						length_input = $("input[name='f2r_column_length_" + field_id + "']");

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


	var setting_controller = false;
	var a = 0;
	jQuery(document).bind('ajaxComplete ', function (event, xhr, settings) {
		var a = settings.data.indexOf('type=formidable2rdb'),
			select_types = $(".f2r_map_type");
		if (settings.data.indexOf('type=formidable2rdb') >= 0) {

			$.each(select_types, function (index, value) {
				process_types($, $(this));
			});

			select_types.change(function (e) {
				process_types($, $(this));
			});

			$(".f2r_map_length").change(function () {
				var field_id = $(this).attr("field_id"),
					default_input = $("input[name='f2r_column_default_" + field_id + "']");
				if(default_input.val().length > $(this).val()){
					default_input.val("");
				}
				default_input.attr("maxlength", $(this).val());
			});

			$(".f2r_map_enabled").click(function () {
				var field_id = $(this).attr("field_id"),
					help_tooltip = $("#frm_help_column_enabled_" + field_id);
				if (!$(this).is(':checked')) {
					help_tooltip.show();
				} else {
					help_tooltip.hide();
				}
			});

			$(".f2r_show_repeatable_fields").click(function () {
				var field_id = $(this).attr("field_id"),
					section = $('#f2r_hidden_repeatable_section_'+field_id);
				if (!section.is(':visible')) {
					section.show();
				} else {
					section.hide();
				}
			});

			//Only accept numbers in the length and the precision map
			bind_validation($('.f2r_map_length,.f2r_map_precision'), "[0-9]");

			//Only accept alpha in the name map
			bind_validation($('.f2r_map_name,.f2r_table_name'), "[a-zA-Z0-9-_]");

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
					} else {
						f2r_exist_table(action_id, table_name, table_name_input);
					}
				} else {
					alert(formidable2rdb.table_name_required);
					table_name_input.addClass("f2r_error");
					move(table_name_input);
				}
			});

			setting_controller = true;
		}
	});

});