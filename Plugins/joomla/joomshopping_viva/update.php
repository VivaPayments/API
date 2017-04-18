<?php
$addon = &JTable::getInstance('addon', 'jshop');
$addon->loadAlias("addon_viva");
$addon->set("name","AddonViva");
$addon->set("version","1.0");
$addon->store();
?>