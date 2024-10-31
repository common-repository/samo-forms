<?PHP
function SAMOFORM_install() 
{
    samoforms_rlog(__FUNCTION__);
    
    if ( $opt = SamoOptions::get( "default" ) ) 
    {
        return;
    }
    
    global $wpdb;
    
    // Instala as tabelas
    samoforms_log("INIT Instalando as tabelas");
    
    $query = "DROP TABLE {$wpdb->prefix}samo_post_formularios";
    $wpdb->query($query);
    
    $query = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}samo_post_formularios (
        formulario_codigo int(11) NOT NULL AUTO_INCREMENT,
        formulario_titulo varchar(100) DEFAULT NULL,
        formulario_campos text,
        formulario_campos_backup text,
        formulario_situacao varchar(50) DEFAULT NULL,
        formulario_nome varchar(100) DEFAULT NULL,
        formulario_email varchar(100) DEFAULT NULL,
        formulario_emailresposta varchar(100) DEFAULT NULL,
        formulario_css varchar(50) DEFAULT NULL,
        formulario_msg_sucesso varchar(100) DEFAULT NULL,
        formulario_datacriacao datetime DEFAULT NULL,
        formulario_autor BIGINT(20) NULL DEFAULT NULL,
        PRIMARY KEY (formulario_codigo)
    )   ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Tabela com as informações dos formulários criados'
    ";
    $wpdb->query($query);
    
    $query = "DROP TABLE {$wpdb->prefix}samo_post_respostas";
    $wpdb->query($query);
    
    $query = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}samo_post_respostas (
        resposta_codigo int(5) NOT NULL AUTO_INCREMENT,
        resposta_datahora datetime DEFAULT NULL,
        resposta_conteudo text,
        resposta_formulario int(5) DEFAULT NULL,
        resposta_emailenviado varchar(100) DEFAULT NULL,
        resposta_datahoraenvio datetime DEFAULT NULL,
        resposta_respostaenvio text,
        resposta_navegador text,
        PRIMARY KEY (resposta_codigo)
    )   ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Tabela com as informações preenchidas por pessoas'
    ";
    $wpdb->query($query);
    
    samoforms_log("END Instalando as tabelas");
    
    // Atualiza o config com informações básicas
    SamoOptions::set( "default",
        array(
            "timestamp" => current_time( "timestamp" ),
            "version" => SAMOFORMS_VERSION
        )
    );
    
}


function SAMOFORM_upgrade() 
{
    samoforms_rlog(__FUNCTION__);
    
    if ( !$opt = SamoOptions::get( "default" ) || version_compare($opt['version'], SAMOFORMS_VERSION, ">=") ) {
        return;
    }
    
    global $wpdb;    
    
    // Atualiza o config com informações básicas
    SamoOptions::set( "default",
        array(
            "timestamp" => current_time( "timestamp" ),
            "version" => SAMOFORMS_VERSION
        )
    );
    
}