<?php
/**
 * Frontend Module Skeleton for Contao Open Source CMS
 *
 * Copyright (C) 2017 Intelligent Spark
 *
 * @package    Contao Module Skeleton
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Register PSR-0 namespace
 */
if (class_exists('NamespaceClassLoader')) {
    NamespaceClassLoader::add('IntelligentSpark', 'system/modules/contao-square-payments/library');
}

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'mod_square'                  => 'system/modules/contao-square-payments/templates/modules'
));