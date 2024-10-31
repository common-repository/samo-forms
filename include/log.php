<?PHP
function samoforms_log($logText='') 
{
    if(! is_string($logText) ){
        ob_start();
            var_dump($logText);
            $logText = ob_get_contents();
        ob_end_clean();
    }
    
    $logText .= "\n Date: ".date('d/m/Y H:i:s')."\n\n";
    
    file_put_contents(SAMOFORMS_DIR .'/log.txt', $logText,  FILE_APPEND );
}

function samoforms_rlog($logText='') 
{
    static $run;
    
    $run =  ((int)$run) + 1;
    
    $logText = addslashes($logText);
   // echo "<script>console.info('".date("H:i:s")."($run) - {$logText}')</script>";
}