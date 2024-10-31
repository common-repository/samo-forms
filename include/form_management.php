<?php
if ( ! class_exists( "WP_List_Table" ) ) {
    require_once( ABSPATH . "wp-admin/includes/class-wp-list-table.php" );
}

class SAMOFORM_form_table extends WP_List_Table {
    
    public function __construct() {
        parent::__construct( array(
            "singular" => "test",
            "plural" => "tests",
            "ajax" => false
        ));
    }
    
    public static function get_forms($search="", $per_page = 5, $page_number = 1 ) {

        global $wpdb;
        
        $offset     = ( $page_number - 1 ) * $per_page;
        $orderBy    = "";
        
        if ( ! empty( $_REQUEST["orderby"] ) ) {
            $orderBy .= " ORDER BY form." . esc_sql( $_REQUEST["orderby"] );
            $orderBy .= ! empty( $_REQUEST["order"] ) ? " " . esc_sql( $_REQUEST["order"] ) : " ASC";
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
            {$wpdb->prefix}samo_post_formularios form
            INNER JOIN {$wpdb->prefix}users user 
                ON form.formulario_autor = user.ID
        WHERE
            {$condWhere}
        {$orderBy}
        LIMIT 
            {$per_page}
        OFFSET 
            {$offset}
        ";
        return $wpdb->get_results( $sql, "ARRAY_A" );
    }
    
    public static function get_countForms($search="") {

        global $wpdb;
        
        $condWhere = "1=1";

        if( !empty($search) ){
            $filterWhere = esc_sql( $search );
            $condWhere .= " AND formulario_titulo LIKE '%{$filterWhere}%'"; 
        }
        
        $sql = "
        SELECT 
            count(*) 
        FROM 
            {$wpdb->prefix}samo_post_formularios form
            INNER JOIN {$wpdb->prefix}users user 
                ON form.formulario_autor = user.ID
        WHERE
            {$condWhere}
        ";
        return $wpdb->get_var( $sql );
    }
    
    /** Overwrires **/
    function get_columns() {
        $columns = array(
            "formulario_titulo"         => "Formulário",
            "author_name"               => "Author",
            "formulario_situacao"       => "Situação",
            "formulario_datacriacao"    => "Criado em",
            "formulario_action1"        => "Atalho WP",
            "formulario_action2"        => "Gerar HTML",
        );
        return $columns;
    }
    
    public function get_sortable_columns() {
        $sortable_columns = array(
            "formulario_titulo" => array( "formulario_titulo", true ),
            "formulario_situacao" => array( "formulario_situacao", false ),
            "formulario_datacriacao" => array( "formulario_datacriacao", false ),
        );

        return $sortable_columns;
    }

    function column_default( $item, $column_name ) {
        
        switch($column_name)
        {
            case "formulario_action1":
                return "<code>[SAMOFORMS id='{$item["formulario_codigo"]}']</code>";
                break;
                
            case "formulario_action2":
                return "<a href='javascript:openCode(\"{$item["formulario_codigo"]}\");'>Incorporar código</a>";
                break;
            
            default:
            if( isset($item[ $column_name ]) )
                return $item[ $column_name ];
                return "<strong>Valor não encontrado!</strong>";
                break;
        }
    }
    
    function column_formulario_titulo($item) {
        $actions = array(
            "edit"      => sprintf("<a href='?page=%s&action=%s&register=%s'>Editar</a>",$_REQUEST["page"],"form",$item["formulario_codigo"]),
            "delete"    => sprintf("<a href='?page=%s&action=%s&register=%s' onclick='if(!confirm(\"O formulário indicado será deletado!\")) return false;'>Deletar</a>",$_REQUEST["page"],"delete",$item["formulario_codigo"]),
            "view"      => sprintf("<a href='?page=%s&action=%s&register=%s'>Mensagens</a>",$_REQUEST["page"],"view",$item["formulario_codigo"]),
        );

      return sprintf('%1$s %2$s', $item["formulario_titulo"], $this->row_actions($actions) );
    }
    
    function column_formulario_datacriacao($item) {
        return date("d/m/Y \à\s H\hi", strtotime($item["formulario_datacriacao"]));
    }
    
    function column_author_name($item) {
        return $item["display_name"];
    }
    function column_formulario_situacao($item) {
        switch($item["formulario_situacao"])
        {
            case "ALIVE":
                $situacao = "Ativo";
                break;
            case "INCOMA":
                $situacao = "Rascunho";
                break;
            case "DEAD":
                $situacao = "Excluido";
                break;
            default:
                $situacao = $item["formulario_situacao"];
                break;
        }
        return $situacao;
    }
    
    function prepare_items() {
        $current_page   = $this->get_pagenum();
        $per_page       = $this->get_items_per_page( "samoforms_per_page", 5 );
        $columns        = $this->get_columns();
        $hidden         = array();
        $sortable       = $this->get_sortable_columns();
        
        //$this->_column_headers = array($columns, $hidden, $sortable);
        $this->_column_headers = $this->get_column_info();
        
        // Filtro
        $search = "";
        if(isset($_REQUEST["s"]) && !empty($_REQUEST["s"]) ){
            $search = $_REQUEST["s"];
        }
        
        $this->set_pagination_args( array("total_items" => self::get_countForms($search), "per_page" => $per_page) );
        $this->items = self::get_forms($search, $per_page, $current_page);
    }
    
    function get_bulk_actions() {
        $actions = array(
            "delete" => "Deletar",
        );

        return array(); //$actions;
    }
    
    function old($name, $default){
        
        // Procura em arrays
        if( is_array($name) ){
            $procura = $_POST[$name[0]];
            $name = $name[1];
        }else{
            $procura = $_POST;
        }
        
        if( isset($procura[$name]) ){
            return $procura[$name];
        }
        else if(isset($default[$name]) ){
             return $default[$name];
        }
    }
}

/**
 * Plugin settings page
 */
function SAMOFORM_menuAberto_formManagement_setScreem( $status, $option, $value ) {
    return $value;
}
add_filter( "set-screen-option", "SAMOFORM_menuAberto_formManagement_setScreem", 10, 3 );

function SAMOFORM_menuAberto_formManagement(){
    samoforms_rlog(__FUNCTION__);
    
    global $forms_obj, $wpdb, $drivers;
    
    $action = isset($_REQUEST["action"]) ?$_REQUEST["action"]: "view";
    $action = esc_attr($action);

    $register = (int) esc_attr(isset($_REQUEST["register"]) ?$_REQUEST["register"] : 0);
    
    // Salva os campos
    if(isset($_POST["SAMOForm"]) && $action == "form"){
        
        $data = [
            "formulario_titulo" => filter_var($_POST["SAMOForm"]["formulario_titulo"], FILTER_SANITIZE_STRING),
            "formulario_campos" => json_encode($_POST["SAMOInput"]),
            "formulario_situacao" => "ALIVE",
            "formulario_nome" => filter_var($_POST["SAMOForm"]["formulario_nome"], FILTER_SANITIZE_STRING),
            "formulario_email" => filter_var($_POST["SAMOForm"]["formulario_email"], FILTER_SANITIZE_EMAIL),
            "formulario_msg_sucesso" => filter_var($_POST["SAMOForm"]["formulario_msg_sucesso"], FILTER_SANITIZE_STRING),
            "formulario_emailresposta" => "",
            "formulario_css" => "",
            "formulario_datacriacao" => date("Y-m-d H:i:s"),
            "formulario_autor" => get_current_user_id(),
        ];
            
        if($register){
            $wpdb->update("{$wpdb->prefix}samo_post_formularios", $data, ["formulario_codigo"=>$register]);
        }else{
            $wpdb->insert("{$wpdb->prefix}samo_post_formularios", $data);
            $register = $wpdb->insert_id;
        }
        
        samoforms_successMessage("Informações salvas com sucesso!");
        header("Location: admin.php?page=idSamoFormsForm&action=form&register=$register");
        exit;
        
    }
    
    if($action == "delete" && $register){
        
        $wpdb->delete("{$wpdb->prefix}samo_post_formularios", ["formulario_codigo"=>$register]);
        header("Location: admin.php?page=idSamoFormsForm");
        exit;
    }
    
    if($action == "view"){
        $args = [
            "label"   => "Formulários por página",
            "default" => 5,
            "option"  => "samoforms_per_page"
        ];

        add_screen_option( "per_page", $args );
    }
    
    $forms_obj = new SAMOFORM_form_table();
}
 
function SAMOFORM_form_management() {
    samoforms_rlog(__FUNCTION__);
    
    global $forms_obj, $wpdb, $drivers;
    
    $forms_obj = new SAMOFORM_form_table();
    
    $options = get_option( "SAMOForm_options" );
    $action = isset($_REQUEST["action"]) ?$_REQUEST["action"]: "view";
    $action = esc_attr($action);
    
    $register = (int) esc_attr(isset($_REQUEST["register"]) ?$_REQUEST["register"] : 0);
    $defaults = ["formulario_nome"=>$options["nome"],"formulario_email"=>$options["email"]];
    
    if($action == "form" && $register){
        $defaults = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}samo_post_formularios WHERE formulario_codigo = {$register} LIMIT 1", ARRAY_A);
    }
    
    samoforms_successMessage();
    wp_enqueue_script( 'sortable', plugins_url('html.sortable.min.js', __FILE__));
    ?>
    
    <?PHP if( in_array($action, ["view"]) ){ ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Formulários</h1>
        <?php 
        if(!$options) {
            samoforms_doError("É necessário configurar os parâmetros primeiro!");
        }
        ?>
        
        
        <?php if($options) { ?>
        <a href="admin.php?page=idSamoFormsForm&action=form" class="page-title-action">
            Novo formulário
        </a>
        <hr class="wp-header-end">
        <form method="get" action="">
            <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST["page"] ); ?>" />
            <?php
            $forms_obj->prepare_items();
            $forms_obj->search_box("Pesquisar formulários", "samoforms_search" );
            $forms_obj->display();
            ?>
        </form>
            
        <div id="modalSAMOForm">
            <div class="container">
                <h2>Copie o script abaixo: <a href="javascript:closeCode();">X</a></h2>
                <code class="code">
                    
                </code>
                <template class="template">
&lt;div id='SAMOFORM-root-%id%'>Aguarde...&lt;/div&gt;<br/>
&lt;script&gt;(function(d, s) {
    var js, SF = d.getElementsByTagName(s)[0];
    var id = 'SAMOFORM-script-%id%';
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = '<?PHP echo admin_url("admin-ajax.php?action=SAMORender&SAMOFormRender=%id%"); ?>';
    SF.parentNode.insertBefore(js, SF);
}(document, 'script'));
&lt;script&gt;
                </template>
            </div>
        </div>
        
        <script type="text/javascript">
        function openCode(id){
            try{
                var objRaiz = document.getElementById("modalSAMOForm");
                    objRaiz.style.display = 'initial';
                    
                var objTemplate = document.querySelector("#modalSAMOForm .template");
                var objCode = document.querySelector("#modalSAMOForm .code");
                    objCode.innerHTML = objTemplate.innerHTML.replace(/%id%/gi, id)
                
                var objAlvo = document.querySelector("#modalSAMOForm .container");
                if (objAlvo.classList)
                  objAlvo.classList.add("openSamoForm");
                else
                  objAlvo.className += " openSamoForm";
            }
            catch(e){
                console.table(e);
            }
        }
        
        function closeCode(){
            try{
                var objRaiz = document.getElementById("modalSAMOForm");
                    objRaiz.style.display = 'none';
                
                var objAlvo = document.querySelector("#modalSAMOForm .container");
                if (objAlvo.classList)
                  objAlvo.classList.remove("openSamoForm");
                else
                  objAlvo.className = el.className.replace(new RegExp('(^|\\b)openSamoForm(\\b|$)', 'gi'), ' ');
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
        
        #modalSAMOForm .container{
            width: 380px;
            height: 240px;
            position: relative;
            left: 50%;
            top: 30%;
            margin-left: -190px;
            margin-top: -120px;
            background: #FFF;
            border-radius: 5px;
            
        }
        
        #modalSAMOForm .container h2{
            text-align: center;
        }       
        
        #modalSAMOForm .container h2 a{
            background: #f00;
            color: white;
            border-radius: 50%;
            position: absolute;
            left: 365px;
            padding: 4px 9px;
            font-size: 14px;
            top: -8px;
            text-decoration: none;
        }
        
        #modalSAMOForm .container h2 a:hover{
            background: #870a0a;
        }
        
        #modalSAMOForm .container code{
            width: 350px;
            height: 190px;
            overflow-y: scroll;
            display: block;
            margin: 10px;
        }
        
        .openSamoForm{
            animation: aberturaQuadro .5s linear 1;
        }
        
        @keyframes aberturaQuadro {
          0% { top: -50%; }
          100% { top: 30%; }
        }
        </style>
        
        <?php } ?>
    </div>
    <?php } ?>
    
    <?PHP if( in_array($action, ["form"]) ){ ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?PHP echo $register?"Editar formulário":"Novo formulário"; ?></h1>
        <a href="admin.php?page=idSamoFormsForm" class="page-title-action">
            Cancelar
        </a>
        <hr class="wp-header-end">
        
        <form action="<?PHP echo esc_attr($_SERVER["REQUEST_URI"]); ?>" method="POST">
        <div id="poststuff">
        
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content" style="position: relative;">
                    <div id="titlediv">
                        <div id="titlewrap">
                            <input name="SAMOForm[formulario_titulo]" size="30" value="<?PHP echo $forms_obj->old(['SAMOForm','formulario_titulo'], $defaults); ?>" id="title" spellcheck="true" autocomplete="off" type="text" placeholder="Titulo do formulário" />
                        </div>
                    </div>
                    
                    <div id="slugdiv" class="postbox hide-if-js" style="display: block;">
                        <h2 class="hndle ui-sortable-handle"><span>Formulário gerado:</span></h2>
                        <div class="inside form-inside" id="samoForm-itens">
                        <?PHP
                        $SAMOInput = json_decode($defaults['formulario_campos'], 1);
                        if($SAMOInput){
                            foreach($SAMOInput as $id => $inputs) {
                            
                                $className = ucfirst( strtolower($inputs['type']) );
                                echo $drivers[$className]->renderOptions($id, $inputs);
                            }
                        }
                        ?>
                        </div>
                    </div>
                    
                </div>
                
                <!-- ATALHOS -->
                <div id="postbox-container-1" class="postbox-container atalhos">
                    <div class="postbox">
                        <h2><span>Novo campo:</span></h2>
                        <div class="inputbox">
                            <div id="top-publishing">
                                <div class="top-publishing-actions">
                                        <select name="SAMOForm[type]" id="samoForm-drives">
                                            <option>Escolha um campo</option>
                                            <?PHP
                                            foreach($drivers as $name => $object)
                                            {
                                                $node = $object->getInfo();
                                                echo "<option value='{$name}'>{$node[alias]}</option>";
                                            }
                                            ?>
                                        </select>
                                    <p><button type="button" id="samoForm-add" class="button button-secondary button-large">Adicionar campo</button></p>
                                    <div class="clear"></div>
                                </div>
                                
                                <div id="misc-publishing-actions"></div>
                            </div>           
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2><span>Quem vai receber?</span></h2>
                        <div class="submitbox">
                            <div id="top-publishing">
                                <div class="top-publishing-actions">
                                    <label>Enviar para: (Nome):</label>
                                    <p><input type="text" name="SAMOForm[formulario_nome]" autocomplete="off" value="<?PHP echo $forms_obj->old(['SAMOForm','formulario_nome'], $defaults); ?>" /></p>
                                    <div class="clear"></div>
                                </div>
                                <div class="top-publishing-actions">
                                    <label>Enviar para: (E-mail)</label>
                                    <p><input type="text" name="SAMOForm[formulario_email]" autocomplete="off" value="<?PHP echo $forms_obj->old(['SAMOForm','formulario_email'], $defaults); ?>" /></p>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2><span>Outras informações</span></h2>
                        <div class="submitbox">
                            <div id="top-publishing">
                                <div class="top-publishing-actions">
                                    <label>Messagem final depois de enviar:</label>
                                    <p><input type="text" name="SAMOForm[formulario_msg_sucesso]" autocomplete="off" value="<?PHP echo $forms_obj->old(['SAMOForm','formulario_msg_sucesso'], $defaults); ?>" /></p>
                                    <div class="clear"></div>
                                </div>
                                
                                <div id="misc-publishing-actions"></div>
                            </div>
                            <div class="clear"></div>
                            <div id="major-publishing-actions">
                                <div id="delete-action">
                                    <a class="submitdelete deletion" href="admin.php?page=idSamoFormsForm&action=delete">Descartar</a>
                                </div>
                                <div id="publishing-action">
                                    <span class="spinner"></span>
                                    <input name="SAMOForm[publish]" id="publish" class="button button-primary button-large" value="Salvar" type="submit">
                                </div>
                                <div class="clear"></div>
                            </div>
             
                        </div>
                    </div>
                </div>
            </div>
        
        </div>
        </form>
        
    </div>
    
    <style type="text/css">
    .js-handle{
        cursor: move;
    }
    
    .atalhos h2{
        font-size: 14px;
        padding: 8px 12px;
        margin: 0;
        line-height: 1.4;
        border-bottom: 1px solid #eee;
    }
    
    .atalhos .top-publishing-actions{
        padding: 10px 10px 0;
    }
    
    .atalhos .top-publishing-actions label{
        font-weight: bold;
    }
    
    .atalhos .top-publishing-actions input, 
    .atalhos .top-publishing-actions button, 
    .atalhos .top-publishing-actions textarea, 
    .atalhos .top-publishing-actions select{
        width: 100%;
    }
    
    .atalhos .inputbox{
        
    }
    
    .form-inside .inside{
        height: 80px;
    }
    
    .form-inside .inside label{
        display: block;
    }
    
    .form-inside .inside::before{
        content: " ";
        clear: both;
    }
    
    .form-inside .inside .inside-colunn-input input[type=text],
    .form-inside .inside .inside-colunn-input input[type=email],
    .form-inside .inside .inside-colunn-input textarea,
    .form-inside .inside .inside-colunn-input select{
        width: 90%;
    }
    
    .form-inside .inside .inside-colunn-input{
        width: 25%;
        float: left;
    }
    
    .form-inside .inside .inside-colunn-action{
        width: 25%;
        float: right;
    }
    
    #slugdiv h2{
        cursor: default;
    }    
    </style>
    
    
    <?PHP
    foreach($drivers as $name => $object)
    {
        echo "<template id='Drive-{$name}'>". $object->renderOptions() ."</template>";
    }
    ?>

    <script>
    function addDrive(drive){
        elm = document.getElementById("Drive-"+drive);
        
        if( elm != "undefined" ){
            id = Math.round(new Date().getTime());
            //document.getElementById("samoForm-itens").innerHTML += elm.innerHTML.replace(/%ID%/g, id);
            //document.getElementById("samoForm-itens").appendChild( elm.innerHTML.replace(/%ID%/g, id) );
            document.getElementById("samoForm-itens").insertAdjacentHTML('beforeend', elm.innerHTML.replace(/%ID%/g, id));
            
            sortable('#samoForm-itens',{
                items:'.itens',
                handle: '.js-handle',
                placeholder: "<div style='height:150px;'></div>"
            });
        }else{
            alert("Drive ainda não implementado!");
        }
    }
    
    document.getElementById('samoForm-add').addEventListener('click', function(){
        driver = document.getElementById('samoForm-drives').value;
        addDrive(driver);
    });
    
    itens = document.querySelector("#samoForm-itens");
    itens.addEventListener('click', function(event){
    
        var elements = itens.querySelectorAll('.samoForm-remove');
        var hasMatch = Array.prototype.indexOf.call(elements, event.target) >= 0;
        
        if(hasMatch){
            //alert(event.target.getAttribute('data-id'));
            id = event.target.getAttribute('data-id');
        
            if(confirm("O elemento será removido, essa ação é irreversivel!")){
                x = document.querySelector("#samoForm-itens .item-"+id);
                x.style.background = '#ff6969';
                x.style.opacity = 1;

                (function fade() {
                    if ((x.style.opacity -= .05) < 0) {
                        x.parentNode.removeChild(x);
                    } else {
                        requestAnimationFrame(fade);
                    }
                })();
            }
        }
    });
    
    jQuery(document).ready(function(){
        sortable('#samoForm-itens',{
            items:'.itens',
            handle: '.js-handle',
            placeholder: "<div style='height:150px;'></div>"
        });
    });
    </script>
    
    <?php } ?>
    
<?php
}