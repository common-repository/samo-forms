<?php
#use PHPMailer\PHPMailer\PHPMailer;
#use PHPMailer\PHPMailer\Exception;

function SAMOFORM_render($attr){
    samoforms_rlog(__FUNCTION__);
    
    global $wpdb;
    
    if(!isset($attr['id']))
        return;
    
    $defaults = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}samo_post_formularios WHERE formulario_codigo = {$attr['id']} LIMIT 1", ARRAY_A);
    $adminUrl = site_url("?SAMOFormRender={$attr['id']}");
    $adminUrl = admin_url("admin-ajax.php?action=SAMORender&SAMOFormRender={$attr['id']}");
    $formRender = "
    <div id='SAMOFORM-root-{$attr['id']}'>Aguarde...</div>
    <script>(function(d, s) {
        var js, SF = d.getElementsByTagName(s)[0];
        var id = 'SAMOFORM-script-{$attr['id']}';
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = '$adminUrl';
        SF.parentNode.insertBefore(js, SF);
    }(document, 'script'));
    </script>";
    return $formRender;
}

function SAMOFORM_renderForm(){
    samoforms_rlog(__FUNCTION__);
    
    global $wpdb, $drivers;
    
    $SAMOForm = (int) (isset($_GET['SAMOFormRender']) && !!$_GET['SAMOFormRender'])?$_GET['SAMOFormRender'] :0;
    $defaults = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}samo_post_formularios WHERE formulario_codigo = {$SAMOForm} LIMIT 1", ARRAY_A);
    
    $formRender = "saida = '';";

    $SAMOInput = json_decode($defaults['formulario_campos'], 1);
    if($SAMOInput){
        foreach($SAMOInput as $id => $inputs) {
        
            $className = ucfirst( strtolower($inputs['type']) );
            $render = samoforms_textJs($drivers[$className]->renderPublic($SAMOForm, $id, $inputs));
            $formRender .= "saida += {$render};";
        }
    }
    
    $replaces = [
        '__ID__' => $SAMOForm,
        '__SAIDA__' => $formRender,
        '__URL__' => site_url("?SAMOFormSubmit={$SAMOForm}"),
        '__URL__' => admin_url("admin-ajax.php?action=SAMOSubmit&SAMOFormSubmit={$SAMOForm}"),
    ];
    
    header('Content-Type: application/javascript');
    echo str_replace( array_keys($replaces), array_values($replaces), file_get_contents(SAMOFORMS_DIR ."/include/action.js"));
    wp_die();
}


function SAMOFORM_submitForm(){
    header('Content-Type: application/javascript');
    header("Access-Control-Allow-Origin: *");
    
    date_default_timezone_set('America/Sao_Paulo');
    
    samoforms_rlog(__FUNCTION__);
    
    // Evita erros
    if(!isset($_POST['SAMOInput']) || !is_array($_POST['SAMOInput'])){
        echo json_encode( ['status'=>500, 'message'=>'Preencha todos campos!'] );
        wp_die();
    }
    
    global $wpdb, $drivers;
        
    $SAMOForm = (int) (isset($_GET['SAMOFormSubmit']) && !!$_GET['SAMOFormSubmit'])?$_GET['SAMOFormSubmit'] :0;
    $defaults = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}samo_post_formularios WHERE formulario_codigo = {$SAMOForm} LIMIT 1", ARRAY_A);
    $SAMOPost = $_POST['SAMOInput'];
    $SAMOOpts = get_option( "SAMOForm_options" );
    $SAVEPost = array();
    
    $replayTo = '';
    $copyTo = '';
    $emailRender = "";

    $SAMOInput = json_decode($defaults['formulario_campos'], 1);
    if($SAMOInput){
        foreach($SAMOInput as $id => $inputs) {
        
            if(!isset($SAMOInput[$id])){
                continue;
            }
            
            $className = ucfirst( strtolower($inputs['type']) );

            try{
                $output = [];
                if( $drivers[$className]->valid($SAMOPost[$id], $inputs, $output) ){
                    $SAVEPost[$output['title']] = $output['value'];
                    $replayTo = (isset($output['replayTo'])) ?$output['replayTo'] :$replayTo;
                    $copyTo = (isset($output['copyTo'])) ?$output['copyTo'] :$copyTo;
                    $render = !empty($SAMOOpts['formatacao_email']) ?$SAMOOpts['formatacao_email'] :$drivers[$className]->renderEmail($SAMOForm, $id, $inputs);
                    $emailRender .= str_replace(["#VALOR#","#TITULO#"], [$output['value'], $output['title']], $render);
                }
            }catch(Exception $e){
                echo json_encode( ['status'=>500, 'message'=> $e->getMessage()] );
                wp_die();
            }
                
        }
    }

    $data = [
        "resposta_datahora" => date("Y-m-d H:i:s"),
        "resposta_conteudo" => json_encode($SAVEPost),
        "resposta_formulario" => $SAMOForm,
        "resposta_emailenviado" => $defaults['formulario_email'],
        "resposta_navegador" => $_SERVER['HTTP_USER_AGENT'],
    ];
        
    $wpdb->insert("{$wpdb->prefix}samo_post_respostas", $data);
    
    if( !$register = $wpdb->insert_id ){
        return json_encode( ['status'=>500, 'message'=>'Por favor tente novamente!'] );
    }

    require ABSPATH . WPINC . '/class-phpmailer.php';
    require ABSPATH . WPINC . '/class-smtp.php';
    
    $logsMailer = "";
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->SMTPDebug    = 2;
    $mail->Debugoutput  = function($str, $level) use(&$logsMailer) {
        $qtd = strlen($logsMailer);
        $logsMailer .= "\n{$qtd}: debug level $level; message: $str";
    };
    
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = $SAMOOpts['servidor']; //'smtp1.backsite.com.br';
        $mail->SMTPAuth = true;
        $mail->Username = $SAMOOpts['usuario']; //'brunoalves@backsite.com.br';
        $mail->Password = $SAMOOpts['senha']; //'d23m02';
        $mail->SMTPAutoTLS  = false;
        $mail->Port = (int) $SAMOOpts['porta'];
        
        switch($SAMOOpts['conexao'])
        {
            case 'tsl':
                $mail->SMTPSecure = 'tls';
                break;
            case 'ssl':
                $mail->SMTPSecure = 'ssl';
                break;
        }
        //$mail->SMTPSecure = 'tls';

        $replayTo       = !empty($replayTo) ?$replayTo :$SAMOOpts['usuario'];
        $addressName    = !empty($defaults['formulario_nome']) ?$defaults['formulario_nome'] :$SAMOOpts['nome'];
        $addressEmail   = !empty($defaults['formulario_email']) ?$defaults['formulario_email'] :$SAMOOpts['email'];
        
        $emailRenderOut = !empty($SAMOOpts['formatacao_global']) ?$SAMOOpts['formatacao_global'] :"<h1>#FORMULARIO#</h1> <hr /> #CORPO#";
        $emailReplace = [
            '#PROTOCOLO#' => $register,
            '#FORMULARIO#' => $defaults['formulario_titulo'],
            '#CORPO#' => $emailRender,
        ];
        $emailRenderOut = str_replace( array_keys($emailReplace), array_values($emailReplace), $emailRenderOut);
        
        //Recipients
        $mail->setFrom($SAMOOpts['usuario'], 'Mailer SAMOPost'); // Quem enviou
        $mail->isHTML(true);
        $mail->Body     = $emailRenderOut;
        $mail->AltBody  = strip_tags($emailRenderOut);
        
        if( !empty($copyTo) ){
            $copia = $mail;
            $copia->addReplyTo($addressEmail, $addressName);    // A quem responder
            $copia->addAddress($copyTo, "Cópia do Cliente");    // Para quem enviar
            $copia->Subject =  date('d/M'). " - Cópia do e-mail - {$defaults['formulario_titulo']}";
            $copia->send();
            unset($copia);
        }
        
        $mail->clearAddresses();
        $mail->clearCCs();
        $mail->clearBCCs();
        $mail->clearReplyTos();
        $mail->clearAllRecipients();
        $mail->addReplyTo($replayTo, 'Cliente');            // A quem responder
        $mail->addAddress($addressEmail, $addressName);     // Para quem enviar
        $mail->Subject = date('d/M'). " - {$defaults['formulario_titulo']}";
        $mail->send();
        unset($mail);
        
    } catch (Exception $e) {

        $data = [
            "resposta_respostaenvio" => "Error: {$mail->ErrorInfo} - {$SAMOOpts['servidor']}\n\n  {$SAMOOpts['usuario']}:{$SAMOOpts['senha']} \n\nLog: {$logsMailer}",
        ];

        $wpdb->update("{$wpdb->prefix}samo_post_respostas", $data, ["resposta_codigo"=>$register]);

        return json_encode( ["status"=>500, "message"=>"Impossivel enviar a sua mensagem!"] );
    }
    
    $data = [
        "resposta_datahoraenvio" => date("Y-m-d H:i:s"),
        "resposta_respostaenvio" => "Log: $logsMailer",
    ];
        
    $wpdb->update("{$wpdb->prefix}samo_post_respostas", $data, ["resposta_codigo"=>$register]);

    $msgSuccess = !empty($defaults['formulario_msg_sucesso']) ?$defaults['formulario_msg_sucesso'] :$SAMOOpts['formatacao_sucesso'];
    $msgSuccess = !empty($msgSuccess) ?$msgSuccess :'Mensagem enviada com sucesso!';
    
    echo json_encode( ['status'=>200, 'message'=> str_replace("#PROTOCOLO#", $register, $msgSuccess)] );
    wp_die();
}


function SAMOFORM_viewForm(){
    header('Content-Type: application/javascript');
    header("Access-Control-Allow-Origin: *");
    
    samoforms_rlog(__FUNCTION__);
        
    global $wpdb, $drivers;

    $SAMOForm = (int) (isset($_GET['SAMOFormView']) && !!$_GET['SAMOFormView'])?$_GET['SAMOFormView'] :0;
    $SAMOQuerie = "
    SELECT 
        formulario.*,
        resposta.* 
    FROM 
        {$wpdb->prefix}samo_post_respostas as resposta

    INNER JOIN 
        {$wpdb->prefix}samo_post_formularios as formulario 
        ON formulario.formulario_codigo = resposta.resposta_formulario

    WHERE
        resposta_codigo = {$SAMOForm} 
    LIMIT 
        1
    ";

    $defaults = $wpdb->get_row($SAMOQuerie, ARRAY_A);
    $SAMOPost = json_decode($defaults['resposta_conteudo'], 1);
    $SAMOOpts = get_option( "SAMOForm_options" );
    $SAVEPost = array();

    $emailRender = "";
    $htmlDefault = "
    <div>
        <strong>#TITULO#</strong>
        #VALOR#
    </div>
    ";
    
    if($SAMOPost){
        foreach($SAMOPost as $title => $value) {              
            $emailRender .= str_replace(["#VALOR#","#TITULO#"], [$value, $title], $htmlDefault);
        }
    }
        
    $emailRenderOut = !empty($SAMOOpts['formatacao_global']) ?$SAMOOpts['formatacao_global'] :"<h1>#FORMULARIO#</h1> <hr /> #CORPO#";
    $emailReplace = [
        '#PROTOCOLO#' => $SAMOForm,
        '#FORMULARIO#' => $defaults['formulario_titulo'],
        '#CORPO#' => $emailRender,
    ];
    $emailRenderOut = str_replace( array_keys($emailReplace), array_values($emailReplace), $emailRenderOut);
    
    echo json_encode( ['status'=>200, 'message'=> $emailRenderOut] );
    wp_die();
}