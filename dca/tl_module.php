<?php
/**
 * Frontend Module Skeleton for Contao Open Source CMS
 *
 * Copyright (C) 2017 Intelligent Spark
 *
 * @package    Contao Module Skeleton
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['square'] = '{title_legend},name,type;{square_legend},personal_access_token,application_id;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests';

$GLOBALS['TL_DCA']['tl_module']['fields']['personal_access_token'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['personal_access_token'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
    'sql'                     => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['application_id'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['application_id'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
    'sql'                     => "varchar(255) NOT NULL default ''"
);