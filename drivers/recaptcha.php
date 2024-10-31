<?php
namespace SAMO;

use Exception;

class Recaptcha implements Drivers
{
    public function getInfo(){
        return [
            'name' => 'recaptcha',
            'alias' => 'Recaptcha (Captcha do Google)'
        ];
    }
    
    public function renderOptions($conf=0, $values=array()){
        
        $title = isset($values['title'])?$values['title'] :"";
        $mostrarTitulo = isset($values['mostrarTitulo']) && $values['mostrarTitulo'] == 'SIM'?" checked" :"";
        $config = isset($values['config'])?$values['config'] :'';
        
        $configLight    = ($config=='light')?' selected' :'';
        $configDark     = ($config=='dark')?' selected' :'';
        
        $html = "
        <div class='itens item-%ID%'>
            <strong draggable='true' class='js-handle'>|| Tipo: Recaptcha</strong>
            <div class='inside'>
                <div class='inside-colunn-input'>
                    <label>Título:</label>
                    <input name='SAMOInput[%ID%][title]' size='13' value='$title' type='text' />
                    <input name='SAMOInput[%ID%][type]' value='Recaptcha' type='hidden' />
                </div>
                <div class='inside-colunn-input'>
                    <label>Tema:</label>
                    <select name='SAMOInput[%ID%][config]'>
                        <option value='light'$configLight>Light (Claro)</option>
                        <option value='dark'$configDark>Dark (Escuro)</option>
                    </select>
                </div>
                <div class='inside-colunn-input'>
                    <label>Opções:</label>
                    <label><input type='checkbox' name='SAMOInput[%ID%][mostrarTitulo]' value='SIM' $mostrarTitulo/> Mostrar título</label>
                </div>
                <div class='inside-colunn-action'>
                    <label>Ação:</label>
                    <input type='button' value='Remover' class='button button-mini samoForm-remove' data-id='%ID%' />
                </div>
            </div>
        </div>
        ";
        
        return ($conf) ?str_replace("%ID%", $conf, $html ):$html;
    }
    
    public function renderPublic($form=0, $conf=0, $values=array()){
        
        $options = get_option( 'SAMOForm_options' );
        $title = isset($values['title'])?$values['title'] :"";
        $mostrarTitulo = isset($values['mostrarTitulo']) && $values['mostrarTitulo'] == 'SIM';
        $config = isset($values['config'])?$values['config'] :'';
        $configTheme = ($config!='light')?'dark' :'light';
        
        if($mostrarTitulo){
            $html = "
            <div class='SAMOFORM-itens item-{$conf}'>
                <div class='inside-colunn-name'>
                    <label>$title</label>
                </div>
                <div class='inside-colunn-input'>
                    <input type='hidden' name='SAMOInput[$conf]' id='SAMOInput-$conf' value='Validação'/>
                    <div class='g-recaptcha' data-sitekey='{$options['recaptcha_publica']}' data-theme='{$configTheme}'></div>
                </div>
            </div>
            <script src='http://www.google.com/recaptcha/api.js?hl=pt-BR'></script>
            ";
        }else{
            $html = "
            <div class='SAMOFORM-itens item-{$conf}'>
                <div class='inside-colunn-input'>
                    <input type='hidden' name='SAMOInput[$conf]' id='SAMOInput-$conf' value='Validação'/>
                    <div class='g-recaptcha' data-sitekey='{$options['recaptcha_publica']}' data-theme='{$configTheme}'></div>
                </div>
            </div>
            <script src='http://www.google.com/recaptcha/api.js?hl=pt-BR'></script>
            ";
        }
        
        return $html;
    }
    
    public function valid($value=null, $values=array(), &$output=array()){
        
        $options = get_option( 'SAMOForm_options' );
        
        # https://gist.github.com/jonathanstark/dfb30bdfb522318fc819
        # Verify captcha
        $_REMOTE_ADDR = (isset($_SERVER["HTTP_CF_CONNECTING_IP"]))?$_SERVER["HTTP_CF_CONNECTING_IP"] :$_SERVER['REMOTE_ADDR'];
        
        $post_data = http_build_query(
            array(
                'secret' => $options['recaptcha_privada'],
                'response' => $_POST['g-recaptcha-response'],
                'remoteip' => $_REMOTE_ADDR
            )
        );
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $post_data
            )
        );
        $context  = stream_context_create($opts);
        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        $result = json_decode($response);
        if ($result->success) {
            $output = ['title'=>$values['title'], 'value'=>"Opção respondida!", "renderEmail"=>false];
            return false;
        }
        throw new Exception("Campo {$values['title']} é inválido");
    }
}
