<?php
/*
Demonstrates running the CJ export functionality and saving the output to S3.

*/
//require_once $argv[1].'app/Mage.php';
require_once './app/Mage.php';

umask(0);
Mage::App()->loadArea('admin');
Mage::getSingleton('core/session', array('name'=>'adminhtml'));
$cj = new Phillro_Commissionjunction_Helper_Data();
$buffer= $cj->generateProductCSVHeader();
$buffer.= $cj->generateProductCSVBody();
file_put_contents('media/cj_export.csv',$buffer);

$adapter=Mage::getSingleton('imagecdn/adapter_amazons3');
$adapter->save('/cj_export.csv','media/cj_export.csv');
?>
