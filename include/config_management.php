<?php
/**
 * @internal never define functions inside callbacks.
 * these functions could be run multiple times; this would result in a fatal error.
 */
 
/**
 * custom option and settings
 */
function samo_settings_init() {
    
    // new setting
    register_setting( "SAMOFormConfig", 'SAMOForm_options' );

    $opt = SamoOptions::get( "default" );

    // new setting
    add_settings_section("samoform_section_sender", "[Importante] Informações de envio:", "", "SAMOFormConfig");
    add_settings_field("servidor", "Endereço do servidor", "samo_field_pill_cb", "SAMOFormConfig", "samoform_section_sender", ["name_input"=>"servidor"]);
    add_settings_field("usuario", "Usuário", "samo_field_pill_cb", "SAMOFormConfig", "samoform_section_sender", ["name_input"=>"usuario"]);
    add_settings_field("senha", "Senha", "samo_password_pill_cb", "SAMOFormConfig", "samoform_section_sender", ["name_input"=>"senha"]);
    add_settings_field("porta", "Porta", "samo_select_pill_cb", "SAMOFormConfig", "samoform_section_sender", ["name_input"=>"porta", "options"=>["587"=>"587","25"=>"25","465"=>"465"]]);
    add_settings_field("conexao", "Conexão", "samo_select_pill_cb", "SAMOFormConfig", "samoform_section_sender", ["name_input"=>"conexao", "options"=>["tsl"=>"Segura usando TSL (Recomendados)","ssl"=>"Segura usando SSL","none"=>"sem segurança"]]);
    
    // new setting
    add_settings_section("samoform_section_receiver", "[Importante] Informações de recebimento:", "", "SAMOFormConfig");
    add_settings_field("nome", "Nome", "samo_field_pill_cb", "SAMOFormConfig", "samoform_section_receiver", ["name_input"=>"nome"]);
    add_settings_field("email", "E-mail", "samo_field_pill_cb", "SAMOFormConfig", "samoform_section_receiver", ["name_input"=>"email"]);
    
    // new setting
    add_settings_section("samoform_section_recaptcha", "[Opcional] Informações do ReCaptcha:", "", "SAMOFormConfig");
    add_settings_field("recaptcha_publica", "Chave publica", "samo_field_pill_cb", "SAMOFormConfig", "samoform_section_recaptcha", ["name_input"=>"recaptcha_publica"]);
    add_settings_field("recaptcha_privada", "Chave privada", "samo_field_pill_cb", "SAMOFormConfig", "samoform_section_recaptcha", ["name_input"=>"recaptcha_privada", "help_input"=>"Para usar o reCAPTCHA deve obter uma chave API do <a target=\"_blank\" href=\"https://www.google.com/recaptcha/admin\">Google reCAPTCHA</a>"]);
    
    // new setting
    add_settings_section("samoform_section_formatting", "[Opcional] Formatações:", "", "SAMOFormConfig");
    add_settings_field("formatacao_global", "Conteudo do e-mail", "samo_text_pill_cb", "SAMOFormConfig", "samoform_section_formatting", ["name_input"=>"formatacao_global", "help_input"=>"(#PROTOCOLO#: Código do protocolo, #FORMULARIO#: Titulo do formulário, #CORPO#: Corpo do e-mail repetido para cada campo)"]);
    add_settings_field("formatacao_email", "Corpo do e-mail", "samo_text_pill_cb", "SAMOFormConfig", "samoform_section_formatting", ["name_input"=>"formatacao_email", "help_input"=>"(#TITULO#: Titulo do campo, #VALOR#: Valor preenchido pelo usuário)"]);
    add_settings_field("formatacao_messagem", "Mensagem de sucesso", "samo_text_pill_cb", "SAMOFormConfig", "samoform_section_formatting", ["name_input"=>"formatacao_sucesso", "help_input"=>"(#PROTOCOLO#: Código do protocolo)"]);
    
    // new setting
    add_settings_section("samoform_section_version", "[Informação] Versão:", "", "SAMOFormConfig");
    add_settings_field("version_instalada", "Versão instalada", "samo_label_pill_cb", "SAMOFormConfig", "samoform_section_version", ["help_input"=>$opt['version']]);
    add_settings_field("version_atual", "Versão atual", "samo_label_pill_cb", "SAMOFormConfig", "samoform_section_version", ["help_input"=>SAMOFORMS_VERSION . " - " .(( version_compare($opt['version'], SAMOFORMS_VERSION, "<")) ?"Necessário atualizar":"Tudo correto" ) ]);
    
}
 
/**
 * register our samo_settings_init to the admin_init action hook
 */
add_action( 'admin_init', 'samo_settings_init' );
 
// pill field cb
function samo_label_pill_cb( $args ) {
    
    // get the value of the setting we've registered with register_setting()
    echo "<p>{$args['help_input']}</p>";
}

// pill field cb
function samo_field_pill_cb( $args ) {
    
    // get the value of the setting we've registered with register_setting()
    $options = get_option( 'SAMOForm_options' );
    $value = $options && isset($options[$args['name_input']]) ?$options[$args['name_input']] :"";
    echo "<input type='text' name='SAMOForm_options[{$args['name_input']}]' class='regular-text' value='$value' />";
    
    if( isset($args['help_input']) ){
        echo "<p>{$args['help_input']}</p>";
    }
}

// pill password cb
function samo_password_pill_cb( $args ) {
    
    // get the value of the setting we've registered with register_setting()
    $options = get_option( 'SAMOForm_options' );
    $value = $options && isset($options[$args['name_input']]) ?$options[$args['name_input']] :"";
    echo "<input type='password' name='SAMOForm_options[{$args['name_input']}]' class='regular-text' value='$value' />";
    
    if( isset($args['help_input']) ){
        echo "<p>{$args['help_input']}</p>";
    }
}

// pill text cb
function samo_text_pill_cb( $args ) {
    
    // get the value of the setting we've registered with register_setting()
    $options = get_option( 'SAMOForm_options' );
    $value = $options && isset($options[$args['name_input']]) ?$options[$args['name_input']] :"";
    echo "<textarea name='SAMOForm_options[{$args['name_input']}]' class='regular-text'>$value</textarea>";
    
    if( isset($args['help_input']) ){
        echo "<p>{$args['help_input']}</p>";
    }
}

// pill seelct cb
function samo_select_pill_cb( $args ) {
    
    // get the value of the setting we've registered with register_setting()
    $options = get_option( 'SAMOForm_options' );
    $value = $options && isset($options[$args['name_input']]) ?$options[$args['name_input']] :"";
    echo "<select name='SAMOForm_options[{$args['name_input']}]' class='regular-text'>";
    
    foreach($args['options'] as $key => $node){
        $selected = $value==$key ?" selected":"";
        echo "<option value='$key'$selected>$node</option>";
    }
    
    echo "</select>";
    echo "<p>{$args['help_input']}</p>";
}
 
/**
 * top level menu
 * add top level menu page
 */
//add_action( 'admin_menu', 'samo_options_page' );
//function samo_options_page() {
//  add_menu_page("Configurações do servidor de e-mail", "Configurar", "manage_options", "SAMOFormConfig", "samo_options_page_html");
//}
 
/**
 * top level menu:
 * callback functions
 */
function SAMOFORM_config_management() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // add error/update messages
    if ( isset( $_GET['settings-updated'] ) )
        samoforms_doSuccess("Configurações salvas com sucesso!");
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
        <?php
        settings_fields( 'SAMOFormConfig' );
        do_settings_sections( 'SAMOFormConfig' );
        submit_button( 'Salvar configurações' );
        ?>
        </form>
    </div>
    <?php
}