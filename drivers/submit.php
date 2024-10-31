<?php
namespace SAMO;

use Exception;

class Submit implements Drivers
{
    public function getInfo(){
        return [
            'name' => 'submit',
            'alias' => 'Botão de enviar'
        ];
    }
    
    public function renderOptions($conf=0, $values=array()){
        
        $title = isset($values['title'])?$values['title'] :"";
        $config = isset($values['config'])?$values['config'] :'';
        
        $html = "
        <div class='itens item-%ID%'>
            <strong draggable='true' class='js-handle'>|| Tipo: Submit</strong>
            <div class='inside'>
                <div class='inside-colunn-input'>
                    <label>Título:</label>
                    <input name='SAMOInput[%ID%][title]' size='13' value='$title' type='text' />
                    <input name='SAMOInput[%ID%][type]' value='Submit' type='hidden' />
                </div>
                <div class='inside-colunn-input'>
                    <label>CallbackJS:</label>
                    <input name='SAMOInput[%ID%][config]' size='13' value='$config' type='text' />
                </div>
                <!--div class='inside-colunn-input'>
                    <label>Opções:</label>
                    <label><input type='checkbox' name='SAMOInput[%ID%][obrigatorio]' value='NAO' $obrigatorio/> Não obrigatório</label>
                </div-->
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
        
        $title = isset($values['title'])?$values['title'] :"";
        $config = isset($values['config']) && !empty($values['config'])?$values['config'] :"SendForm{$form}(this)";
        
        $html = "
        <div class='SAMOFORM-itens item-{$conf}'>
            <div class='inside-colunn-action'>
                <button type='button' name='SAMOInput[{$conf}]' id='SAMOInput-{$form}' onclick='$config'>$title</button>
            </div>
        </div>
        ";
        
        return $html;
    }
    
    public function valid($value=null, $values=array(), &$output=array()){
        return false;
    }
}
