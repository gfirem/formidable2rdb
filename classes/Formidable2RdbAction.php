<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Formidable2RdbAction extends FrmFormAction {
	
	protected $form_default = array( 'wrk_name' => '' );
	private $error = array();
	/** @var Formidable2mysql */
	private $rdb_instance;
	private $tree_walker;
	private $current_action = false;
	
	public function __construct() {
		if ( class_exists( "FrmProAppController" ) ) {
			require_once 'Formidable2RdbCore.php';
			require_once 'core/TreeWalker.php';
			require_once "Formidable2RdbException.php";
			
			$rdb_core = new Formidable2RdbCore( array(
				"driver" => "mysql",
				"host"   => DB_HOST,
				"dbname" => DB_NAME,
				"user"   => DB_USER,
				"pass"   => DB_PASSWORD,
				"debug"  => true,
			) );
			
			$this->rdb_instance = $rdb_core->getHandler();
			
			$this->tree_walker = new TreeWalker( array(
					"debug"      => true,
					"returntype" => "array"
				)
			);
			
			add_filter( 'frm_validate_form', array( $this, 'validate_form' ), 20, 2 );
			
			add_filter( 'frm_before_save_formidable2rdb_action', array( $this, 'before_save_action' ), 10, 5 );
			add_action( 'frm_update_form', array( $this, 'update_form' ), 10, 2 );
			
			add_action( 'before_delete_post', array( $this, 'delete_action' ), 10, 1 );
			
			add_action( 'frm_trigger_formidable2rdb_create_action', array( $this, 'f2m_action_create' ), 10, 3 );
			add_action( 'frm_trigger_formidable2rdb_update_action', array( $this, 'f2m_action_update' ), 10, 3 );
			
			add_action( 'admin_head', array( $this, 'add_admin_styles' ) );
			add_filter( 'wp_kses_allowed_html', array( $this, 'wp_kses_allowed_html' ), 10, 2 );
			add_shortcode( "form-f2m-security", array( $this, 'form_f2m_security_content' ) );
			
			$action_ops = array(
				'classes'  => 'f2rdb_integration',
				'limit'    => 99,
				'active'   => true,
				'priority' => 50,
				'event'    => array( 'create', 'update', 'delete', 'import' ),
			);
			
			$this->FrmFormAction( 'formidable2rdb', "Formidable2Rdb", $action_ops );
		}
	}
	
	/**
	 * Drop table when action is delete
	 *
	 * @param $post_id
	 */
	public function delete_action( $post_id ) {
		if ( ! empty( $post_id ) ) {
			$post = get_post( $post_id );
			if ( ! empty( $post ) && $post->post_type == "frm_form_actions" ) {
				$content = $post->post_content;
				if ( ! empty( $content ) ) {
					$content = FrmAppHelper::maybe_json_decode( $content );
					if ( ! empty( $content["f2r_table_name"] ) && ! empty( $this->rdb_instance ) ) {
						$table_name      = $content["f2r_table_name"];
						$full_table_name = Formidable2RdbAdmin::get_table_name( $table_name );
						$site_id         = is_multisite() ? get_current_blog_id() : false;
						$track_tables    = Formidable2RdbTrackTables::get_track_table_by_name( $table_name );
						if ( $track_tables["action_id"] == $post_id && ! empty( $table_name ) && ! empty( $this->rdb_instance )
						     && $table_name == $track_tables["table"] && $this->rdb_instance->exist_table( Formidable2RdbAdmin::get_table_name( $table_name ) )
						) {
							/**
							 * Execute before drop table name
							 *
							 * @param $full_table_name String Complete table name
							 * @param $table_name String The custom part of the name
							 * @param $site_id Integer|Boolean The site Id or false is single site
							 * @param $post_id Integer The action id
							 *
							 */
							do_action( "formidable2rdb_before_drop_table", $full_table_name, $table_name, $site_id, $post_id );
							//Drop table
							$this->rdb_instance->drop_table( Formidable2RdbAdmin::get_table_name( $table_name ) );
							/**
							 * Execute after drop table name
							 *
							 * @param $full_table_name String Complete table name
							 * @param $table_name String The custom part of the name
							 * @param $site_id Integer|Boolean The site Id or false is single site
							 * @param $post_id Integer The action id
							 *
							 */
							do_action( "formidable2rdb_after_drop_table", $full_table_name, $table_name, $site_id, $post_id );
							
						}
					}
				}
			}
		}
	}
	
	/**
	 * Save action settings
	 *
	 * @param $settings
	 *
	 * @return int|WP_Error|WP_Post
	 */
	public function save_settings( $settings ) {
		if ( ! empty( $settings["post_content"]["f2r_mapped_field"] ) ) {
			$settings["post_content"]["f2r_old_mapped_field"] = $settings["post_content"]["f2r_mapped_field"];
		}
		
		return parent::save_settings( $settings );
	}
	
	/**
	 * Get all Formidable2RdbAction belong to the form
	 *
	 * @param $form_id
	 *
	 * @return array|mixed
	 */
	public function get_actions( $form_id ) {
		return FrmProPostAction::get_action_for_form( $form_id, "formidable2rdb" );
	}
	
	public function update_form( $form_id, $values ) {
		if ( ! empty( $values["frm_action"] ) ) {
			$this->current_action = $values["frm_action"];
		}
	}
	
	public function before_save_action( $instance_post_content, $instance, $new_instance, $old_instance, $current ) {
		if ( ! empty( $this->current_action ) ) {
			switch ( $this->current_action ) {
				case "update_settings": //Executed when action is updated
					
					break;
				case "update"://Executed when form is save, only process removed fields
					$mapped_to_rdb = Formidable2mysqlColumnFactory::import_json( $instance_post_content["f2r_mapped_field"], true );
					$new_fields    = FrmField::get_all_for_form( $instance_post_content["form_id"] );
					$fields        = array();
					foreach ( $new_fields as $new_field_key => $new_field_value ) {
						if ( ! in_array( $new_field_value->type, Formidable2RdbAdmin::exclude_fields() ) ) {
							$fields[ $new_field_value->id ] = $new_field_value->field_key;
						}
					}
					$mapped_to_save = array();
					foreach ( $mapped_to_rdb as $mapped_to_rdb_key => $mapped_to_rdb_val ) {
						if ( ! array_key_exists( $mapped_to_rdb_key, $fields ) ) {
							unset( $mapped_to_rdb[ $mapped_to_rdb_key ] );
						} else {
							$mapped_to_save[] = $mapped_to_rdb_val;
						}
					}
					$instance_post_content["f2r_mapped_field"] = json_encode( $mapped_to_save );
					$this->process_table_columns( $instance_post_content["form_id"], $instance_post_content );
					break;
			}
		}
		
		return $instance_post_content;
	}
	
	/**
	 * Validate field if are required to run the action and apply changes to table structure if is necessary
	 *
	 * @param $errors
	 * @param $values
	 *
	 * @return mixed
	 */
	public function validate_form( $errors, $values ) {
		if ( ! empty( $values["frm_action"] ) ) {
			$this->current_action = $values["frm_action"];
			switch ( $values["frm_action"] ) {
				case "update_settings": //Executed when action is updated
					$r = $this->complete_process_table_structure( $values );
					if ( $r !== true ) {
						$errors = array_merge( $errors, array( "0" => $r ) );
					}
					break;
				case "update"://Executed when form is saved
					$has = FrmFormAction::form_has_action_type( $values['id'], "formidable2rdb" );
					if ( $has ) {
						$actions = FrmProPostAction::get_action_for_form( $values['id'], "formidable2rdb" );
						if ( ! empty( $actions ) ) {
							foreach ( $actions as $key => $action ) {
								$post            = get_post( $action, ARRAY_A );
								$actions[ $key ] = $post;
							}
						}
						$_POST["frm_formidable2rdb_action"] = $actions;
						$this->update_callback( $values['id'] );
					}
					break;
			}
		}
		
		return $errors;
	}
	
	/**
	 * Process mapped field to rdb structure
	 *
	 * @param $values
	 *
	 * @return bool
	 */
	private function complete_process_table_structure( $values ) {
		try {
			if ( ! empty( $values["frm_formidable2rdb_action"] ) ) {
				foreach ( $values["frm_formidable2rdb_action"] as $action_id => $action_prop ) {
					if ( ! empty( $this->rdb_instance ) && ! empty( $action_prop["post_content"] ) ) {
						//Change table name if is necessary
						if ( ! empty( $action_prop["post_content"]["f2r_table_name"] ) && ! empty( $action_prop["post_content"]["f2r_old_table_name"] ) ) {
							if ( $action_prop["post_content"]["f2r_table_name"] != $action_prop["post_content"]["f2r_old_table_name"] ) {
								$this->rdb_instance->rename_table( Formidable2RdbAdmin::get_table_name( $action_prop["post_content"]["f2r_old_table_name"] ), Formidable2RdbAdmin::get_table_name( $action_prop["post_content"]["f2r_table_name"] ) );
							}
						}
						
						$this->process_table_columns( $values["id"], $action_prop["post_content"], $action_id );
					}
					unset( $action_id, $action_prop );
				}
			}
		} catch ( Formidable2RdbException $ex ) {
			return $this->handle_exception( $ex->getMessage(), $ex->getBody() );
		} catch ( Exception $ex ) {
			return $this->handle_exception( $ex->getMessage() );
		}
		
		return true;
	}
	
	/**
	 * Process the difference of columns or create table
	 *
	 * @param $form_id
	 * @param $post_content
	 * @param $action_id
	 * @param bool $raw
	 */
	private function process_table_columns( $form_id, $post_content, $action_id = 0, $raw = false ) {
		if ( ! empty( $post_content["f2r_table_name"] ) && ! empty( $post_content["f2r_mapped_field"] ) ) {
			$mapped_to_rdb   = Formidable2mysqlColumnFactory::import_json( $post_content["f2r_mapped_field"], $raw );
			$table_name      = $post_content["f2r_table_name"];
			$full_table_name = Formidable2RdbAdmin::get_table_name( $post_content["f2r_table_name"] );
			$site_id         = is_multisite() ? get_current_blog_id() : false;
			if ( ! empty( $mapped_to_rdb ) ) {
				if ( ! $this->rdb_instance->exist_table( $full_table_name ) ) {
					/**
					 * Execute before create table name
					 *
					 * @param $full_table_name String Complete table name
					 * @param $table_name String The custom part of the name
					 * @param $site_id Integer|Boolean The site Id or false is single site
					 * @param $action_id Integer The action id
					 *
					 */
					do_action( "formidable2rdb_before_add_table", $full_table_name, $table_name, $site_id, $action_id );
					//Create a table if not exist
					$this->rdb_instance->create_table( $full_table_name, $mapped_to_rdb );
					/**
					 * Execute after create table name
					 *
					 * @param $full_table_name String Complete table name
					 * @param $table_name String The custom part of the name
					 * @param $site_id Integer|Boolean The site Id or false is single site
					 * @param $action_id Integer The action id
					 *
					 */
					do_action( "formidable2rdb_after_add_table", $full_table_name, $table_name, $site_id, $action_id );
				} else {
					if ( ! empty( $post_content["f2r_old_mapped_field"] ) ) {//detect changes into the mapped fields
						$old_mapped_to_rdb = Formidable2mysqlColumnFactory::import_json( $post_content["f2r_old_mapped_field"], $raw );
						if ( ! empty( $old_mapped_to_rdb ) && ! empty( $mapped_to_rdb ) ) {
							$diff_columns = $this->tree_walker->getdiff( $mapped_to_rdb, $old_mapped_to_rdb, true );
							if ( $this->rdb_instance->exist_table( $full_table_name ) ) {
								if ( ! empty( $diff_columns ) ) {
									$add    = array();
									$remove = array();
									$change = array();
									if ( ! empty( $diff_columns["new"] ) ) {
										foreach ( $diff_columns["new"] as $new_key => $new_item ) {
											$add[] = new Formidable2mysqlColumn( $new_item["Field"], $new_item["Type"], $new_item["Length"], $new_item["Precision"], $new_item["Null"], "", $new_item["Default"], "", $new_item["Enabled"], $new_item["Id"], true );
											unset( $new_key, $new_item );
										}
									}
									if ( ! empty( $diff_columns["removed"] ) ) {
										foreach ( $diff_columns["removed"] as $removed_key => $removed_item ) {
											$remove[] = new Formidable2mysqlColumn( $removed_item["Field"], $removed_item["Type"], $removed_item["Length"], $removed_item["Precision"], $removed_item["Null"], "", $removed_item["Default"], "", $removed_item["Enabled"], $removed_item["Id"], true );
											unset( $removed_key, $removed_item );
										}
									}
									if ( ! empty( $diff_columns["edited"] ) ) {
										foreach ( $diff_columns["edited"] as $edited_key => $edited_item ) {
											$source                   = $old_mapped_to_rdb[ $edited_key ];
											$target                   = $mapped_to_rdb[ $edited_key ];
											$change[ $source->Field ] = $target;
											unset( $edited_key, $edited_item );
										}
									}
									//Alter table with the new changes
									$this->rdb_instance->alter_table( $full_table_name, array(
											"add"    => $add,
											"drop"   => $remove,
											"change" => $change,
										)
									);
								}
							}
						}
					}
				}
			}
		}
	}
	
	
	/**
	 * Handle exceptions
	 *
	 * @param $message
	 * @param null $body
	 */
	private function handle_exception( $message, $body = null ) {
		if ( ! empty( $body ) && is_array( $body ) ) {
			$error_str = "";
			foreach ( $body as $key => $value ) {
				if ( ! empty( $value ) ) {
					$error_str .= $key . " : " . $value . "<br/>";
				}
			}
			
			Formidable2RdbLog::log( array(
				'action'         => "F2R_Management",
				'object_type'    => Formidable2RdbManager::getShort(),
				'object_subtype' => "detail_error",
				'object_name'    => $message,
			) );
			
			Formidable2RdbAdmin::setMessage( array(
				"message" => $message,
				"type"    => "danger"
			) );
			
			return $message;
		}
		$this->show_error( $message );
		
		return $message;
	}
	
	/**
	 * Output error
	 *
	 * @param $string
	 * @param string $type
	 */
	public function show_error( $string, $type = "danger" ) {
		Formidable2RdbAdmin::setMessage( array(
			"message" => $string,
			"type"    => $type
		) );
	}
	
	/**
	 * Convert mapped field to base RDB Column
	 *
	 * @param $mapped
	 * @param $form_id
	 * @param bool $is_old
	 *
	 * @return array
	 */
	public function convert_mapped_to_rdb( $mapped, $form_id, $is_old = false ) {
		$columns    = array();
		$new_fields = FrmField::get_all_for_form( $form_id );
		$fields     = array();
		foreach ( $new_fields as $new_field_key => $new_field_value ) {
			if ( ! in_array( $new_field_value->type, Formidable2RdbAdmin::exclude_fields() ) ) {
				$fields[ $new_field_value->id ] = $new_field_value->field_key;
			}
		}
		foreach ( $mapped as $values_key => $values_val ) {
			foreach ( $fields as $new_field_key => $new_field_value ) {
				//TODO Adicionar para saltar los campos que estan exlcuidos
				if ( "f2r_column_field_id_" . $new_field_value == $values_val->name ) {
					$map_field_id = $values_val->value;
				}
				if ( "f2r_map_enabled_" . $new_field_value == $values_val->name ) {
					$map_enabled = $values_val->value;
				}
				if ( "f2r_column_type_" . $new_field_value == $values_val->name ) {
					$map_type = $values_val->value;
				}
				if ( "f2r_column_name_" . $new_field_value == $values_val->name ) {
					$map_name = $values_val->value;
				}
				if ( "f2r_column_default_" . $new_field_value == $values_val->name ) {
					$map_default = ( ! empty( $values_val->value ) ) ? $values_val->value : "-";
				}
				if ( "f2r_column_type_" . $new_field_value == $values_val->name ) {
					$map_type = $values_val->value;
				}
				if ( "f2r_column_length_" . $new_field_value == $values_val->name ) {
					$map_length = ( ! empty( $values_val->value ) ) ? $values_val->value : "-";
				}
				if ( "f2r_column_precision_" . $new_field_value == $values_val->name ) {
					$map_precision = ( ! empty( $values_val->value ) ) ? $values_val->value : "-";
				}
				if ( "f2r_column_not_null_" . $new_field_value == $values_val->name ) {
					$map_is_null = $values_val->value;
				}
				
				if ( ! empty( $map_enabled ) && $map_enabled == "1" && ! empty( $map_field_id ) && ! empty( $map_name )
				     && ! empty( $map_type ) && $map_type != "none" && ! empty( $map_is_null ) && ! empty( $map_length )
				     && ! empty( $map_default ) && ! empty( $map_precision )
				) {
					switch ( $map_type ) {
						case "VARCHAR":
							if ( ! empty( $map_length ) && $map_length != "-" ) {
								$map_type = "VARCHAR(" . $map_length . ")";
							} else {
								$map_type = "VARCHAR(100)";
							}
							break;
						case "FLOAT":
							if ( ! empty( $map_length ) && $map_length != "-" && ! empty( $map_precision ) && $map_precision != "-" ) {
								$map_type = "FLOAT(" . $map_length . ", " . $map_precision . ")";
							} else {
								$map_type = "FLOAT(20, 2)";
							}
							break;
						case "DATETIME":
						case "TIMESTAMP":
						case "LONGTEXT":
							break;
						default:
							if ( ! empty( $map_length ) && $map_length != "-" ) {
								$map_type = $map_type . "(" . $map_length . ")";
							}
							break;
					}
					$map_default              = ( $map_default == "-" ) ? "" : $map_default;
					$columns[ $map_field_id ] = new Formidable2mysqlColumn( $map_name, $map_type, $map_is_null, "", $map_default );
					$map_field_id             = "";
					$map_enabled              = "";
					$map_name                 = "";
					$map_default              = "";
					$map_length               = "";
					$map_precision            = "";
					$map_type                 = "";
					$map_is_null              = "";
				}
				unset( $new_field_key, $new_field_value );
				
			}
			if ( $is_old ) {
				if ( strpos( $values_val->name, "f2r_column_field_id_" ) !== false ) {
					$map_field_id = $values_val->value;
				}
				if ( strpos( $values_val->name, "f2r_column_name_" ) !== false ) {
					$map_name = $values_val->value;
				}
				if ( ! empty( $map_field_id ) && ! empty( $map_name ) ) {
					$columns[ $map_field_id ] = new Formidable2mysqlColumn( $map_name, "" );
				}
			}
			unset( $values_key, $values_val );
		}
		
		return $columns;
	}
	
	/**
	 * Triggered by create action
	 *
	 * @param $action
	 * @param $entry
	 * @param $form
	 */
	public function f2m_action_create( $action, $entry, $form ) {
		$this->send_to_mysql( $action, $entry, $form );
	}
	
	/**
	 * Triggered by update action
	 *
	 * @param $action
	 * @param $entry
	 * @param $form
	 */
	public function f2m_action_update( $action, $entry, $form ) {
		$this->send_to_mysql( $action, $entry, $form );
	}
	
	/**
	 * Process the mysql action
	 *
	 * @param $action
	 * @param $entry
	 * @param $form
	 */
	private function send_to_mysql( $action, $entry, $form ) {
		
	}
	
	private function send_action( $action, $entry, $form ) {
		$result = false;
		try {
			$args          = array();
			$campaign_name = "";
			$sender        = "";
			$subject       = "";
			$text_content  = "";
			$html_content  = "";
			$action_fields = array( "campaign_name", "subject", "sender", "segmentation_id", "text_content", "html_content" );
			
			if ( ! empty( $action->post_content["segmentation_enabled"] ) ) {
				if ( empty( $action->post_content["segmentation_id_manually"] ) ) {
					$segmentation_list_content = FrmEntryMeta::get_entry_meta_by_field( $entry->id, $action->post_content["segmentation_id"] );
					$segmentation_list_id      = strval( FormidableMailJetSegmentField::process_content( $segmentation_list_content, array(), true ) );
				} else {
					$segmentation_list_id = strval( $action->post_content["segmentation_id_manually"] );
				}
			} else {
				$segmentation_list_id = "-1";
			}
			
			if ( empty( $action->post_content["contact_list_id_manually"] ) ) {
				$contact_list_content = FrmEntryMeta::get_entry_meta_by_field( $entry->id, $action->post_content["contact_list_id"] );
				$contact_list_id      = strval( FormidableMailJetContactField::process_content( $contact_list_content, array(), true ) );
			} else {
				$contact_list_id = strval( $action->post_content["contact_list_id_manually"] );
			}
			
			$sender_random = false;
			if ( ! empty( $action->post_content["sender_random"] ) ) {
				$sender_random = true;
			}
			
			$schedule = "";
			if ( ! empty( $action->post_content["send_schedule"] ) ) {
				$schedule_content = FrmEntryMeta::get_entry_meta_by_field( $entry->id, $action->post_content["send_schedule"] );
				$format_time      = DateTime::createFromFormat( "Y/m/d H:i", $schedule_content );
				if ( $format_time !== false ) {
					$schedule = $format_time->getTimestamp();
				}
			}
			
			foreach ( $action_fields as $act_field ) {
				$act_content = $action->post_content[ $act_field ];
				$shortCodes  = FrmFieldsHelper::get_shortcodes( $act_content, $entry->form_id );
				$content     = apply_filters( 'frm_replace_content_shortcodes', $act_content, FrmEntry::getOne( $entry->id ), $shortCodes );
				FrmProFieldsHelper::replace_non_standard_formidable_shortcodes( array(), $content );
				$args[ $act_field ] = do_shortcode( $content );
			}
			
			extract( $args );
			$mj_sender = new MailJetSend();
			$result    = $mj_sender->send_campaign( $campaign_name, $sender, $subject, $contact_list_id, $segmentation_list_id, $text_content, $html_content, $sender_random, $schedule );
			
			if ( $result !== false ) {
				$status_fields = FrmField::get_all_types_in_form( $form->id, "mailjet_status" );
				if ( ! empty( $status_fields ) ) {
					if ( empty( $schedule ) ) {
						$campaign_overview = $mj_sender->overview_newsletter( $result["ID"] );
						$status_data       = $campaign_overview[0];
					} else {
						$status_data["NewsLetterID"] = $result["ID"];
						$status_data["LastUpdate"]   = time();
					}
					foreach ( $status_fields as $field ) {
						$value = FrmEntryMeta::get_entry_meta_by_field( $entry->id, $field->id );
						if ( empty( $value ) ) {
							$insert_result = FrmEntryMeta::add_entry_meta( $entry->id, $field->id, null, json_encode( $status_data ) );
						} else {
							$insert_result = FrmEntryMeta::update_entry_meta( $entry->id, $field->id, null, json_encode( $status_data ) );
						}
					}
				}
				FormidableMailJetAdmin::setMessage( array(
					"message" => FormidableMailJetManager::t( "All fine!" ),
					"type"    => "success",
				
				) );
			}
			
		} catch ( FormidableMailJetException $ex ) {
			$this->handle_exception( $ex->getMessage(), $ex->getBody() );
		} catch ( InvalidArgumentException $ex ) {
			$this->handle_exception( $ex->getMessage() );
		}
		
		return $result;
	}
	
	
	/**
	 * Allow new tags to process shortCodes
	 *
	 * @param $allowedPostTags
	 * @param $context
	 *
	 * @return mixed
	 */
	public function wp_kses_allowed_html( $allowedPostTags, $context ) {
		if ( $context == 'post' ) {
			$allowedPostTags['input']['form-f2m-security'] = 1;
			$allowedPostTags['input']['value']             = 1;
		}
		
		return $allowedPostTags;
	}
	
	/**
	 * Return nonce for given action in shortCode
	 *
	 * @param $attr
	 * @param null $content
	 *
	 * @return string
	 */
	public function form_f2m_security_content( $attr, $content = null ) {
		$internal_attr = shortcode_atts( array(
			'act' => 'get_form_field',
		), $attr );
		
		$nonce = base64_encode( $internal_attr['act'] );
		
		return $nonce;
	}
	
	public function add_admin_styles() {
		$current_screen = get_current_screen();
		if ( ! empty( $current_screen ) && $current_screen->id === 'toplevel_page_formidable' ) {
			$icon_url = F2M_IMAGE_PATH . "icon-24.png";
			?>
            <style>
                .frm_formidable2rdb_action.frm_bstooltip.frm_active_action.f2rdb_integration {
                    display: inline-table;
                    font-size: 13px;
                    height: 24px;
                    width: 24px;
                    background-image: url("<?php echo "$icon_url"; ?>");
                    background-repeat: no-repeat;
                }

                .frm_form_action_icon.f2rdb_integration {
                    background-image: url("<?php echo "$icon_url"; ?>");
                    background-repeat: no-repeat;
                    display: block;
                    float: left;
                    font-size: 13px;
                    height: 24px;
                    margin-right: 8px;
                    width: 24px;
                }

                .frm_actions_list > li > a::before, .frm_email_settings h3 .frm_form_action_icon::before {
                    vertical-align: baseline !important;
                }
            </style>
			<?php
		}
	}
	
	/**
	 * Get the HTML for your action settings
	 *
	 * @param array $form_action
	 * @param array $args
	 *
	 * @return string|void
	 */
	public function form( $form_action, $args = array() ) {
		global $wpdb;
		extract( $args );
		$form           = $args['form'];
		$fields         = $args['values']['fields'];
		$action_control = $this;
		
		$form_action->post_content["f2r_old_table_name"]   = $form_action->post_content["f2r_table_name"];
		$form_action->post_content["f2r_old_mapped_field"] = $form_action->post_content["f2r_mapped_field"];
		
		$table_structure_container_css = "";
		
		if ( empty( $form_action->post_content["f2r_table_name"] ) ) {
			$table_structure_container_css = "table_structure_container";
		}
		
		include F2M_VIEW_PATH . 'action.php';
	}
	
	/**
	 * Add the default values for your options here
	 */
	function get_defaults() {
		$result = array(
			'form_id'              => $this->get_field_name( 'form_id' ),
			'f2r_table_name'       => '',
			'f2r_old_table_name'   => '',
			'f2r_mapped_field'     => '',
			'f2r_old_mapped_field' => '',
			'f2r_old_field'        => '',
		);
		
		if ( $this->form_id != null ) {
			$result['form_id'] = $this->form_id;
		}
		
		return $result;
	}
	
}