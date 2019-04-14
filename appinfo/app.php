<?php
/**
 * Load Javascrip
 */
use OCP\Util;
$eventDispatcher = \OC::$server->getEventDispatcher();
$eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function(){
    Util::addScript('video_converter', 'conversion' );
    Util::addScript('video_converter', 'extraction' );
    Util::addStyle('video_converter', 'style' );
});