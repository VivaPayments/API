<?php
if (!defined('_PS_VERSION_')) {
    exit;
} 
if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'hellaspaylatest.php');
} else {
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'hellaspayold.php');
}
?>