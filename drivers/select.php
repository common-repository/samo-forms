<?php
namespace SAMO;

use Exception;

class Select implements Drivers
{
    public function getInfo(){
        return [
            'name' => 'select',
            'alias' => 'Campo de opções'
        ];
    }
    
    public function renderOptions($conf=0, $values=array()){
        
        $title = isset($values['title'])?$values['title'] :"";
        $obrigatorio = isset($values['obrigatorio']) && $values['obrigatorio'] == 'NAO'?" checked" :"";
        $config = isset($values['config'])?$values['config'] :"";
        
        $html = "
        <div class='itens item-%ID%'>
            <strong draggable='true' class='js-handle'>|| Tipo: Select</strong>
            <div class='inside'>
                <div class='inside-colunn-input'>
                    <label>Título:</label>
                    <input name='SAMOInput[%ID%][title]' size='13' value='$title' type='text' />
                    <input name='SAMOInput[%ID%][type]' value='Select' type='hidden' />
                </div>
                <div class='inside-colunn-input'>
                    <label>Opções:</label>
                    <textarea name='SAMOInput[%ID%][config]' size='13'>$config</textarea>
                </div>
                <div class='inside-colunn-input'>
                    <label>Opções:</label>
                    <label><input type='checkbox' name='SAMOInput[%ID%][obrigatorio]' value='NAO' $obrigatorio/> Não obrigatório</label>
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
        
        $title = isset($values['title'])?$values['title'] :"";
        $obrigatorio = isset($values['obrigatorio']) && $values['obrigatorio'] == 'NAO'?"" :" required";
        $config = isset($values['config'])?$values['config'] :'';
        
        $config = explode("\n", $config);
        $option = "<option value=''>Selecione</option>";
        
        foreach($config as $value => $options){
            
            if(preg_match("|\[([^\]]+)\]|", $options, $output)){
                if(!empty($output[1])){
                    $value = $output[1];
                }
            }
            
            preg_match("|^([^<\[]+)|", $options, $output);
            $node = $output[1];
            
            $option .= "<option value='{$value}'>{$node}</option>";
        }
        
        $html = "
        <div class='SAMOFORM-itens item-{$conf}'>
            <div class='inside-colunn-name'>
                <label>$title</label>
            </div>
            <div class='inside-colunn-input'>
                <select name='SAMOInput[{$conf}]' id='SAMOInput-{$conf}' $obrigatorio>
                $option
                </select>
            </div>
        </div>
        ";
        
        return $html;
    }
    
    public function renderEmail($form=0, $conf=0, $values=array()){
        
        $title = isset($values['title'])?$values['title'] :"";
        $obrigatorio = isset($values['obrigatorio']) && $values['obrigatorio'] == 'NAO'?"" :" required";
        $config = isset($values['config'])?$values['config'] :'';
        
        $html = "
        <div>
            <label>#TITULO#</label>
            #VALOR#
        </div>
        <br />
        ";
        
        return $html;
    }
    
    public function valid($value=null, $values=array(), &$output=array()){
        
        $value = trim($value);
        $config = isset($values['config'])?$values['config'] :'';
        $config = explode("\n", $config);
        
        foreach($config as $valueNode => $option){
            
            if(preg_match("|\[([^\]]+)\]|", $option, $output)){
                if(!empty($output[1])){
                    $valueNode = $output[1];
                }
            }
            
            preg_match("|^([^<\[]+)|", $option, $output);
            $node = $output[1];
            
            if(trim($valueNode) == $value){
                $output = ['title'=>$values['title'], 'value'=>$node];
                return true;
            }
        }
        
        throw new Exception("Campo {$values['title']} é inválido");
    }
}
