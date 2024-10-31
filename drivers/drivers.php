<?php
namespace SAMO;

interface Drivers
{
    public function getInfo();
    public function renderOptions($conf=0, $values=array());
    public function valid($value=null, $values=array(), &$output=array());
}