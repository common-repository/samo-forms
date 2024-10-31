<?PHP
/**
 * @package SAMO
 * @version 1.0.0
 */

/*
Plugin Name: SAMO Forms
Plugin URI: http://forms.sistemasamo.com.br
Description: Plugins para criação de uma servidor de criação de formulários para serem usados dentro do wordpress ou incorporados a outros sites
Author: Bruno Alves<contato@alvesbruno.com>
Version: 1.0.0
Author URI: http://brunoalves.sistemasamo.com.br
*/

// Constantes
define("SAMOFORMS_DIR", dirname(__FILE__));
define("SAMOFORMS_FILE", __FILE__);
define("SAMOFORMS_VERSION", "1.0.0");
define("SAMOFORMS_TITLE", "SAMO Forms");
define("SAMOFORMS_NAME", plugin_basename( SAMOFORMS_FILE ) );

// Carrega funcoes secundarias
require SAMOFORMS_DIR ."/include/utils.php";
require SAMOFORMS_DIR ."/include/log.php";
require SAMOFORMS_DIR ."/include/install.php";
require SAMOFORMS_DIR ."/include/menu.php";
require SAMOFORMS_DIR ."/include/form_management.php";
require SAMOFORMS_DIR ."/include/render_management.php";
require SAMOFORMS_DIR ."/include/message_management.php";
require SAMOFORMS_DIR ."/include/config_management.php";
require SAMOFORMS_DIR ."/vendor/samo/posts/samo-options.php";

// Carrega os Drives
$drivers = [];
require SAMOFORMS_DIR ."/drivers/drivers.php";

foreach( glob(SAMOFORMS_DIR ."/drivers/*.php") as $driver){
    if( basename($driver) == "drivers.php")
        continue;

    require $driver;

    $className = ucfirst(basename($driver, '.php'));
    $loadClass = "\\SAMO\\$className";
    $drivers[$className] = new $loadClass();
}

/** Funcao de instalacao */
function SAMOFORM_loaded(){
    samoforms_rlog(__FUNCTION__);
}

function SAMOFORM_init(){
    samoforms_rlog(__FUNCTION__);
}

function SAMOFORM_initAdmin(){
    samoforms_rlog(__FUNCTION__);
}

// Funcões básicas
add_action("activate_". SAMOFORMS_NAME, "SAMOFORM_install" );
add_action("init", "SAMOFORM_init" );
add_action("plugins_loaded", "SAMOFORM_loaded" );
add_action("admin_menu", "SAMOFORM_menu", 9 );
add_action("admin_init", "SAMOFORM_initAdmin" );
add_shortcode("SAMOFORMS", "SAMOFORM_render");

add_action("wp_ajax_SAMOSubmit", "SAMOFORM_submitForm");
add_action("wp_ajax_nopriv_SAMOSubmit", "SAMOFORM_submitForm");

add_action("wp_ajax_SAMORender", "SAMOFORM_renderForm");
add_action("wp_ajax_nopriv_SAMORender", "SAMOFORM_renderForm");

add_action("wp_ajax_SAMOView", "SAMOFORM_viewForm");
add_action("wp_ajax_nopriv_SAMOView", "SAMOFORM_viewForm");

SAMOFORM_upgrade();