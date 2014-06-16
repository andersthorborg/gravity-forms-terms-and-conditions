<?php
/**
 * Plugin Name: Gravity Forms Terms & Conditions
 * Plugin URI: http://thorb.org
 * Description: Adds a "Terms & conditions"-field to gravity forms that requires the user to accept the terms and conditions, shown in an overlay
 * Version: 0.1
 * Author: Anders Thorborg
 * Author URI: http://thorb.org
 * License: GPL2
 */

load_plugin_textdomain('gf_terms_conditions', false, basename( dirname( __FILE__ ) ) . '/languages' );

add_filter("gform_add_field_buttons", "gf_add_terms_conditions_field");

function gf_add_terms_conditions_field($field_groups){
  foreach($field_groups as &$group){
    if($group["name"] == "advanced_fields"){
      $group["fields"][] = array("class"=>"button", "value" => __("Terms", "gravityforms"), "onclick" => "StartAddField('terms_conditions');");
      break;
    }
  }
  return $field_groups;
}

add_filter( 'gform_field_type_title' , 'gf_terms_conditions_title' );
function gf_terms_conditions_title( $type ) {
  if ( $type == 'terms_conditions' ){
    return __( 'Terms & conditions' , 'gravityforms' );
  }
}

add_action( "gform_field_input" , "gf_terms_conditions_field_input", 10, 5 );

function gf_terms_conditions_field_input ( $input, $field, $value, $lead_id, $form_id ){
  if ( $field["type"] == "terms_conditions" ) {
    $input_name = $form_id .'_' . $field["id"];
    $tabindex = GFCommon::get_tabindex();
    $css = isset( $field['cssClass'] ) ? $field['cssClass'] : '';
    if(!is_admin()){
    	$markup = '
<div class="ginput_container">
	<div class="modal fade" id="modal_' . $form_id . '_' . $field['id'] . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	        <h4 class="modal-title">' . $field['terms_conditions_header'] . '</h4>
	      </div>
	      <div class="modal-body">
	        <p>' . nl2br($field['terms_conditions']) . '</p>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">' . __('Close') . '</button>
	      </div>
	    </div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->';
	}
	$link = str_replace('[', '<a href="" data-toggle="modal" data-target="#modal_' . $form_id . '_' . $field['id'] . '">', $field['terms_conditions_link_text']);
	$link = str_replace(']', '</a>', $link);
	$fieldId = $form_id . '_' . $field['id'];
	$markup .= '
	<ul class="gfield_checkbox">
		<li class="gchoice_' . $fieldId . '">
			<input type="checkbox" tabindex="' . $tabindex . '" name="input_' . $field['id'] . '" id="input_' . $fieldId . '">
			<label for="choice_' . $fieldId . '" id="label_' . $fieldId . '">' . $link . '</label>
		</li>
	</ul>
<script type="text/javascript">
	jQuery(function($){
    var modal = $("#modal_' . $form_id . '_' . $field['id'] . '");
    $("body").append(modal);
		var $input = $("#input_' . $form_id . '_' . $field['id'] . '");
		var $form = $input.parents("form");
		var $submit = $form.find("input[type=submit]");
		$submit.attr("disabled", "disabled");
		$input.click(function(){
			if($input.is(":checked")){
				$submit.removeAttr("disabled");
			}
			else{
				$submit.attr("disabled", "disabled");
			}
		});
	});
</script>
</div>
    ';
    // $markup = '<div class="ginput_container"><input data-values=\'' . $field['values'] . '\' type="text" name="input_' . $field['id'] . '" id="input_' . $form_id . '_' . $field['id'] . '" class="gform_terms_conditions no-ui-slider" ' . $tabindex . ' value="' . $value . '" ></div>';
  return $markup;
}
return $input;
}


add_action("gform_field_standard_settings", "gf_terms_conditions_standard_settings", 10, 2);
function gf_terms_conditions_standard_settings($position, $form_id){
  //create settings on position 25 (right after Field Label)
  if($position == 25){
      ?>
      <li class="terms_conditions_header_setting field_setting">
          <label for="field_admin_label">
              <?php _e("Terms and conditions header", "gf_terms_conditions"); ?> <small>(<?php _e('Shown in modal dialog', 'gf_terms_conditions')?>)</small>
          </label>
          <input class="fieldwidth-3" type="text" id="field_terms_conditions_header" onchange="SetFieldProperty('terms_conditions_header', this.value);" value="Terms and conditions"/>
      </li>
      <li class="terms_conditions_setting field_setting">
          <label for="field_admin_label">
              <?php _e("Terms and conditions", "gf_terms_conditions"); ?>
          </label>
          <textarea class="fieldwidth-3 fieldheight-4" rows="20" id="field_terms_conditions" onchange="SetFieldProperty('terms_conditions', this.value);">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Perferendis, sapiente, at, illum quae ullam quia ut mollitia ipsam provident numquam enim fuga culpa quas voluptatum itaque nisi assumenda porro iure!</textarea>
      </li>
      <li class="terms_conditions_link_text_setting field_setting">
          <label for="field_admin_label">
              <?php _e("Terms and conditions link text", "gf_terms_conditions"); ?>
          </label>
          <input class="fieldwidth-3" type="text" id="field_terms_conditions_link_text" onchange="SetFieldProperty('terms_conditions_link_text', this.value);" value="<?php _e('I have read and accepted the [terms and conditions]', 'gf_terms_conditions') ?>"/>
          <p><small><?php _e('Use [brackets] to indicate terms link. Eg. "I have read and accepted the [terms and conditions]."'); ?></small></p>
      </li>
      <?php
  }
}


add_action("gform_editor_js", "gf_terms_conditions_editor_script");
function gf_terms_conditions_editor_script(){
    ?>
    <script type='text/javascript'>
        //adding setting to fields of type "text"
        fieldSettings["terms_conditions"] += ",.label_setting, .terms_conditions_header_setting, .terms_conditions_setting, .terms_conditions_link_text_setting, .admin_label_setting, .size_setting, .default_value_textarea_setting, .error_message_setting, .css_class_setting, .visibility_setting";

        //binding to the load field settings event to initialize the checkbox
        jQuery(document).bind("gform_load_field_settings", function(event, field, form){
        	if(field["terms_conditions"]){
        		jQuery("#field_terms_conditions").val(field["terms_conditions"]);
        	}
            if(field["terms_conditions_header"]){
            	jQuery("#field_terms_conditions_header").val(field["terms_conditions_header"]);
            }
            if(field["terms_conditions_link_text"]){
            	jQuery("#field_terms_conditions_link_text").val(field["terms_conditions_link_text"]);
            }
        });
    </script>
    <?php
}
