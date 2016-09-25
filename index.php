<?php 
require_once "vendor/autoload.php";
use UsamaNoman\PHPEntitiesParser\PHPEntitiesParser;


$A=new PHPEntitiesParser();
 print_r( $A->DateTimeParser("can we see each other 05-12-2015 ? My email address is usama@botsify.com is your the osam@nu.edu.pk"));
print_r($A->NumberParser("ninety two"));
?>