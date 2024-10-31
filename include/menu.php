<?PHP
function SAMOFORM_menu() {
    samoforms_rlog(__FUNCTION__);
    
    $option0 = add_menu_page( 
        "Formulários", // Title 
        "SAMO Forms",
        "manage_options", "idSamoForms",
        "SAMOFORM_form_management", "dashicons-email" );
    add_action("load-{$option0}", "SAMOFORM_menuAberto_formManagement" );
    
    $option1 = add_submenu_page( "idSamoForms",
        "Formulários",
        "Formulários",
        "manage_options", "idSamoFormsForm",
        "SAMOFORM_form_management" );
    add_action("load-{$option1}", "SAMOFORM_menuAberto_formManagement" );
    
    $option2 = add_submenu_page( "idSamoForms",
        "Mensagens",
        "Mensagens",
        "manage_options", "idSamoFormsMessage",
        "SAMOFORM_message_management" );
    add_action("load-{$option2}", "SAMOFORM_menuAberto_messageManagement" );
    
    $option3 = add_submenu_page( "idSamoForms",
        "Configuração",
        "Configuração",
        "manage_options", "idSamoFormsConfig",
        "SAMOFORM_config_management" );
    //add_action("load-{$option3}", "SAMOFORM_menuAberto_configManagement" );
}