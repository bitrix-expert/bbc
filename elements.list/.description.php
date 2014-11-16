<?php
/**
 * Basis components
 *
 * @package components
 * @subpackage basis
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 * @copyright Copyright (c) 2014, Nik Samokhvalov
 */
namespace Components\Basis;

use \Bitrix\Main\Localization\Loc;


if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

Loc::loadMessages(__FILE__);


$arComponentDescription = array(
    'NAME' => Loc::getMessage('ELEMENTS_LIST_DESC_NAME'),
    'DESCRIPTION' => Loc::getMessage('ELEMENTS_LIST_DESC_DESCRIPTION'),
    'SORT' => 20,
    'PATH' => array(
        'ID' => 'basis',
        'NAME' => Loc::getMessage('ELEMENTS_LIST_DESC_GROUP'),
        'SORT' => 10,
        'CHILD' => array(
            'ID' => 'elements',
            'NAME' => Loc::getMessage('ELEMENTS_LIST_DESC_CHILD_GROUP'),
            'SORT' => 10
        )
    ),
);