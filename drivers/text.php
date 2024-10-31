<?php
namespace SAMO;

use Exception;

class Text implements Drivers
{
    public function getInfo(){
        return [
            'name' => 'text',
            'alias' => 'Campo de texto'
        ];
    }
    
    public function renderOptions($conf=0, $values=array()){
        
        $title = isset($values['title'])?$values['title'] :"";
        $obrigatorio = isset($values['obrigatorio']) && $values['obrigatorio'] == 'NAO'?" checked" :"";
        $config = isset($values['config'])?$values['config'] :'';
        
        $configTexto        = ($config=='texto')?' selected' :'';
        $configCep          = ($config=='cep')?' selected' :'';
        $configCpf          = ($config=='cpf')?' selected' :'';
        $configCnpj         = ($config=='cnpj')?' selected' :'';
        $configNumero       = ($config=='numero')?' selected' :'';
        $configTelefone     = ($config=='telefone')?' selected' :'';
        
        $html = "
        <div class='itens item-%ID%'>
            <strong draggable='true' class='js-handle'>|| Tipo: Text</strong>
            <div class='inside'>
                <div class='inside-colunn-input'>
                    <label>Título:</label>
                    <input name='SAMOInput[%ID%][title]' size='13' value='$title' type='text' />
                    <input name='SAMOInput[%ID%][type]' value='Text' type='hidden' />
                </div>
                <div class='inside-colunn-input'>
                    <label>Máscara:</label>
                    <select name='SAMOInput[%ID%][config]'>
                        <option value='texto'$configTexto>Texto</option>
                        <option value='cep'$configCpf>Cep</option>
                        <option value='cpf'$configCnpj>CPF</option>
                        <option value='cnpj'$configCnpj>CNPJ</option>
                        <option value='numero'$configNumero>Números</option>
                        <option value='telefone'$configTelefone>Telefone/Celular</option>
                    </select>
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
        
        $html = "
        <div class='SAMOFORM-itens item-{$conf}'>
            <div class='inside-colunn-name'>
                <label>$title</label>
            </div>
            <div class='inside-colunn-input'>
                <input type='text' name='SAMOInput[$conf]' id='SAMOInput-$conf' $obrigatorio/>
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
        
        if(filter_var($value, FILTER_DEFAULT)){
            $value = filter_var($value, FILTER_SANITIZE_STRING);
            $output = ['title'=>$values['title'], 'value'=>$value];
            return true;
        }
        
        throw new Exception("Campo {$values['title']} é inválido");
    }
}
