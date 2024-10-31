<?php
/**
 * Function to create and display error and success messages
 * @access public
 * @param string session name
 * @param string message
 * @param string display class
 * @return string message
 */
function samoforms_flashMessage( $name = '', $message = '')
{
    if(!isset($_SESSION))
        @session_start();
    
    // Create session 
    if(!isset($_SESSION['utils_flash'])) {
        $_SESSION['utils_flash'] = array();
    }
    
    //We can only do something if the name isn't empty
    if( empty( $name ) )
    { return; }

    //No message, create it
    if( !empty( $message ) && empty( $_SESSION['utils_flash'][$name] ) )
    {
        if( !empty( $_SESSION['utils_flash'][$name] ) )
        {
            unset( $_SESSION['utils_flash'][$name] );
        }

        $_SESSION['utils_flash'][$name] = $message;
    }
    //Message exists, display it
    elseif( !empty( $_SESSION['utils_flash'][$name] ) && empty( $message ) )
    {
        $msg = $_SESSION['utils_flash'][$name];
        unset($_SESSION['utils_flash'][$name]);
        return $msg;
    }
}

function samoforms_successMessage($message = ''){
    
    if( !empty( $message ) )
    {
        samoforms_flashMessage("samo_sucesso", $message);
    }
    elseif($message = samoforms_flashMessage("samo_sucesso")){
        add_settings_error( "samo_sucesso", "samo_sucesso", $message, "updated" );
        settings_errors( "samo_sucesso" );
        
        echo "<script>setTimeout(function(){ document.getElementById('setting-error-samo_sucesso').style.display = 'none'; }, 4000);</script>";
    }
}

function samoforms_doSuccess($message = ''){
    add_settings_error( "samo_sucesso", "samo_sucesso", $message, "updated" );
    settings_errors( "samo_sucesso" );
}

function samoforms_errorMessage($message = ''){
    
    if( !empty( $message ) )
    {
        samoforms_flashMessage("samo_error", $message);
    }
    elseif($message = samoforms_flashMessage("samo_error")){
        add_settings_error( "samo_error", "samo_error", $message, "error" );
        settings_errors( "samo_error" );
        
        echo "<script>setTimeout(function(){ document.getElementById('setting-error-samo_error').style.display = 'none'; }, 4000);</script>";
    }
}

function samoforms_doError($message = ''){
    add_settings_error( "samo_error", "samo_error", $message, "error" );
    settings_errors( "samo_error" );
}

function samoforms_textJs($text){
    $text = preg_replace("@(\n\r|\r\n|\n|\r)@","@#@", $text);
    $text = explode("@#@", $text);
    
    foreach($text as &$line){
        $line = str_replace("\"","\\\"",$line);
        $line = "\"$line\"+";
    }
    
    return join("\n", $text) ."\n\"\"";
}