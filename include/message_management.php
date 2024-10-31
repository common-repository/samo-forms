<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SAMOFORM_message_table extends WP_List_Table {
    
    public function __construct() {
        parent::__construct( array(
            'singular' => 'Mensagem',
            'plural' => 'Mensagens',
            'ajax' => false
        ));
    }
    
    public static function get_messages($search='', $per_page = 5, $page_number = 1 ) {

        global $wpdb;
        
        $offset     = ( $page_number - 1 ) * $per_page;
        $orderBy    = '';
        
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $orderBy .= ' ORDER BY form.' . esc_sql( $_REQUEST['orderby'] );
            $orderBy .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        }
        
        $condWhere = "1=1";

        if( !empty($search) ){
            $filterWhere = esc_sql( $search );
            $condWhere .= " AND form.formulario_titulo LIKE '%{$filterWhere}%'"; 
        }
        
        $sql = "
        SELECT 
            * 
        FROM 
            {$wpdb->prefix}samo_post_respostas resp
            INNER JOIN {$wpdb->prefix}samo_post_formularios form
                ON form.formulario_autor = resp.resposta_formulario
        WHERE
            {$condWhere}
        {$orderBy}
        LIMIT 
            {$per_page}
        OFFSET 
            {$offset}
        ";
        return $wpdb->get_results( $sql, 'ARRAY_A' );
    }
    
    public static function get_countForms($search='') {

        global $wpdb;
        
        $condWhere = "1=1";

        if( !empty($search) ){
            $filterWhere = esc_sql( $search );
            $condWhere .= " AND form.formulario_titulo LIKE '%{$filterWhere}%'"; 
        }
        
        $sql = "
        SELECT 
            count(*) 
        FROM 
            {$wpdb->prefix}samo_post_respostas resp
            INNER JOIN {$wpdb->prefix}samo_post_formularios form
                ON form.formulario_autor = resp.resposta_formulario
        WHERE
            {$condWhere}
        ";
        return $wpdb->get_var( $sql );
    }
    
    /** Overwrires **/
    function get_columns() {
        $columns = array(
            'formulario_titulo'			=> 'Formulário',
            'resposta_datahora'			=> 'Data e Hora',
            'resposta_datahoraenvio'	=> 'Data e Hora envio',
            'formulario_email'			=> 'E-mail enviado',
            "formulario_action"         => 'Ver mensagem',
        );
        return $columns;
    }
    
    public function get_sortable_columns() {
        $sortable_columns = array(
            'formulario_titulo' => array( 'formulario_titulo', true ),
            'formulario_situacao' => array( 'formulario_situacao', false ),
            'resposta_datahora' => array( 'resposta_datahora', false ),
        );

        return array(); //$sortable_columns;
    }

    function column_default( $item, $column_name ) {
        
        switch($column_name)
        {            
            default:
            if( isset($item[ $column_name ]) )
                return $item[ $column_name ];
                return "<strong>Valor não encontrado!</strong>";
                break;
        }
    }

    function column_formulario_titulo($item){
        return "<a href='javascript:openCode({$item['resposta_codigo']})'>{$item['formulario_titulo']}</a>";
    }
    
    function column_formulario_action($item){
        return "<a href='javascript:openCode({$item['resposta_codigo']})'>Ver mensagem</a>";
    }
    
    function column_resposta_datahora($item) {
        return date("d/m/Y \à\s H\hi", strtotime($item["resposta_datahora"]));
    }
    
    function column_resposta_datahoraenvio($item) {
        if( empty($item["resposta_datahoraenvio"]) ){
            return "<strong>Valor não encontrado!</strong>";
        }
        return date("d/m/Y \à\s H\hi", strtotime($item["resposta_datahoraenvio"]));
    }
    
    
    
    function prepare_items() {
        $current_page   = $this->get_pagenum();
        $per_page       = $this->get_items_per_page( 'samoforms_per_page', 5 );
        $columns        = $this->get_columns();
        $hidden         = array();
        $sortable       = $this->get_sortable_columns();
        
        //$this->_column_headers = array($columns, $hidden, $sortable);
        $this->_column_headers = $this->get_column_info();
        
        // Filtro
        $search = '';
        if(isset($_REQUEST['s']) && !empty($_REQUEST['s']) ){
            $search = $_REQUEST['s'];
        }
        
        $this->set_pagination_args( array('total_items' => self::get_countForms($search), 'per_page' => $per_page) );
        $this->items = self::get_messages($search, $per_page, $current_page);
    }
    
    function get_bulk_actions() {
        $actions = array(
            'delete' => "Deletar",
            'reenviar' => "Reenviar",
        );

        return array(); //
    }
}

/**
 * Plugin settings page
 */
function SAMOFORM_menuAberto_messageManagement_setScreem( $status, $option, $value ) {
    return $value;
}
add_filter( "set-screen-option", "SAMOFORM_menuAberto_messageManagement_setScreem", 10, 3 );

function SAMOFORM_menuAberto_messageManagement(){
    samoforms_rlog(__FUNCTION__);
    
    global $forms_objMessage;
    
    $option = 'per_page';
    $args   = [
        'label'   => 'Mensagens por página',
        'default' => 5,
        'option'  => 'samoforms_per_page'
    ];

    add_screen_option( $option, $args );
    $forms_objMessage = new SAMOFORM_message_table();
}
 
function SAMOFORM_message_management() {
    samoforms_rlog(__FUNCTION__);
    
    global $forms_objMessage;
    #$forms_objMessage = new SAMOFORM_message_table();
	$options = get_option( 'SAMOForm_options' );
    ?>
    <div class="wrap">
        <h1>Mensagens recebidas</h1>
        <hr class="wp-header-end">
		
		<?php 
		if(!$options) {
			samoforms_doError("É necessário configurar os parâmetros primeiro!");
		}
		?>
		
		<?php if($options) { ?>
        <form method="get" action="">
            <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
            <?php
            $forms_objMessage->prepare_items();
            $forms_objMessage->search_box("Pesquisar formulários", "samoforms_search" );
            $forms_objMessage->display();
            ?>
        </form>
		<?php } ?>

        <div id="modalSAMOForm">
            <div class="preloader"></div>
            <div class="container">
                <a href="javascript:closeCode();">X</a>
                <div class="message">
                    Aguarde...
                </div>
            </div>
        </div>

        <script type="text/javascript">
        samoAddClass = function(o,c){
            if (o.classList)
                o.classList.add(c);
            else
                o.className += " "+c;
        }

        samoRemClass = function(o,c){
            if (o.classList)
              o.classList.remove(c);
            else
              o.className = o.className.replace(new RegExp('(^|\\b)'+c+'(\\b|$)', 'gi'), ' ');
        }

        function openCode(id){
            
            var request = new XMLHttpRequest();
            var objRaiz = document.getElementById("modalSAMOForm");
                objRaiz.style.display = 'initial';
            
            var objLOAD = document.querySelector("#modalSAMOForm .preloader");
                objLOAD.style.display = 'initial';

            request.open('GET', '<?PHP echo admin_url("admin-ajax.php?action=SAMOView&SAMOFormView="); ?>'+id, true);
            request.onload = function (e) {
                if (request.readyState == 4) 
                {
                    // Check if the get was successful.
                    if (request.status != 200) 
                    {
                        console.error(request.statusText);
                    } else {
                        try{
                            result = eval('('+request.responseText+')');
                            formMessage = document.querySelector("#modalSAMOForm .message");
                            formMessage.innerHTML = result.message;
                                objLOAD.style.display = 'none';
                            var objAlvo = document.querySelector("#modalSAMOForm .container");
                                samoAddClass(objAlvo, "openSamoForm");
                        }
                        catch(e){
                            console.log(request.responseText);
                            console.error(e);
                        }
                    }
                }
            }
            request.onerror = function (e) { console.error(request); console.table(e);  };
            request.send();
        }
        
        function closeCode(){
            try{
                var objRaiz = document.getElementById("modalSAMOForm");
                    objRaiz.style.display = 'none';
            
                var objLOAD = document.querySelector("#modalSAMOForm .preloader");
                    objLOAD.style.display = 'none';
                
                var objAlvo = document.querySelector("#modalSAMOForm .container");
                    samoRemClass(objAlvo, "openSamoForm");
            }
            catch(e){
                console.table(e);
            }
        }
        </script>

        <style type="text/css">
        #modalSAMOForm{
            left: 0;
            top: 0;
            width: 100%;
            height: 100vh;
            background: rgba(0,0,0,0.75);
            position: fixed;
            z-index: 9999;
            display: none;
        }

        #modalSAMOForm .preloader{
            position: relative;
            width: 30px;
            height: 30px;
            left: 50%;
            top: 50%;
            margin-left: -15px;
            margin-top: -15px;
            animation: loading 1s linear infinite;
        }

        #modalSAMOForm .preloader::before{
            content: ' ';
            width: 20px;
            height: 20px;
            background: #FFF;
            display: block;
            position: relative;
            left: -10px;
            top: 10px;
            border-radius: 50%;
            filter: blur(50px);
            animation: loadingBounce 1s linear infinite;
            animation-delay: .5s;
        }

        #modalSAMOForm .preloader::after{
            content: ' ';
            width: 20px;
            height: 20px;
            background: #FFF;
            display: block;
            position: relative;
            left: 20px;
            top: -10px;
            border-radius: 50%;
            animation: loadingBounce 1s linear infinite;
        }
        
        #modalSAMOForm .container{
            width: 680px;
            height: 340px;
            position: relative;
            left: 50%;
            top: 30%;
            margin-left: -340px;
            margin-top: -170px;
            background: #FFF;
            border-radius: 5px;
            display: none;
            
        }  
        
        #modalSAMOForm .container a{
            background: #f00;
            color: white;
            border-radius: 50%;
            position: absolute;
            left: 664px;
            padding: 4px 9px;
            font-size: 14px;
            top: -8px;
            text-decoration: none;
        }
        
        #modalSAMOForm .container a:hover{
            background: #870a0a;
        }
        
        #modalSAMOForm .container .message{
            width: 660px;
            height: 300px;
            overflow-y: scroll;
            display: block;
            margin: 10px;
        }
        
        .openSamoForm{
            display: block !important;
            animation: aberturaQuadro .5s linear 1;
        }

        @keyframes loading {
          0% {  
                -ms-transform: rotate(0deg); /* IE 9 */
                -webkit-transform: rotate(0deg); /* Safari 3-8 */
                transform: rotate(0deg);
            }
          100% {  
                -ms-transform: rotate(360deg); /* IE 9 */
                -webkit-transform: rotate(360deg); /* Safari 3-8 */
                transform: rotate(360deg);
            }
        }

        @keyframes loadingBounce {
          0% {  
                filter: blur(1px);
            }
          50% {  
                filter: blur(5px);
            }
          100% {  
                filter: blur(1px);
            }
        }
        
        @keyframes aberturaQuadro {
          0% { top: -50%; }
          100% { top: 30%; }
        }
        </style>


    </div>
<?php
}