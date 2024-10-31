<?php
class SamoOptions
{
    public static function get($option, $default=false)
    {
        $opt = get_option( "SAMOFORM" );
        if ($opt === false OR !isset($opt[$option]) )
            return $default;
        
        return $opt[$option];
    }
    
    public static function set( $name, $value )
    {
        $option = get_option( "SAMOFORM" );
        $option = ( false === $option ) ? array() : (array) $option;
        $option = array_merge( $option, array( $name => $value ) );
        
        update_option( "SAMOFORM", $option );
    }
}
