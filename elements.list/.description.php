<?php

if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

use \Bitrix\Main\Localization\Loc;

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