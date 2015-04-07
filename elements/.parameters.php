<?php

use Bex\Bbc\Helpers\ComponentParameters;
use Bitrix\Iblock;
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

if (!\Bitrix\Main\Loader::includeModule('bex.bbc')) return false;

Loc::loadMessages(__FILE__);

$currentZone = basename(dirname(__DIR__));

/**
 * @global array $arCurrentValues
 */
try
{
    $currentParameters = [
        'GROUPS' => [
            'LIST' => [
                'NAME' => Loc::getMessage('ELEMENTS_GROUP_LIST'),
                'SORT' => '200'
            ],
            'DETAIL' => [
                'NAME' => Loc::getMessage('ELEMENTS_GROUP_DETAIL'),
                'SORT' => '300'
            ]
        ],
        'PARAMETERS' => [
            'SEF_MODE' => [
                'index' => [
                    'NAME' => Loc::getMessage('ELEMENTS_SEF_INDEX'),
                    'DEFAULT' => '',
                    'VARIABLES' => []
                ],
                'section' => [
                    'NAME' => Loc::getMessage('ELEMENTS_SEF_SECTION'),
                    'DEFAULT' => '#SECTION_CODE#/',
                    'VARIABLES' => ['SECTION_CODE']
                ],
                'detail' => [
                    'NAME' => Loc::getMessage('ELEMENTS_SEF_DETAIL'),
                    'DEFAULT' => '#SECTION_CODE#/#ELEMENT_CODE#/',
                    'VARIABLES' => ['ELEMENT_CODE', 'SECTION_CODE']
                ]
            ],
            'USE_SEARCH' => [
                'PARENT' => 'OTHERS',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_PARAMETERS_USE_SEARCH'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ]
        ]
    ];

    $paramsElementsList = ComponentParameters::getParameters(
        $currentZone.':elements.list',
        [
            'SECTION_ID' => [
                'DELETE' => true
            ],
            'SECTION_CODE' => [
                'DELETE' => true
            ],
            'SELECT_FIELDS' => [
                'RENAME' => 'LIST_SELECT_FIELDS',
                'MOVE' => 'LIST'
            ],
            'SELECT_PROPS' => [
                'RENAME' => 'LIST_SELECT_PROPS',
                'MOVE' => 'LIST'
            ],
            'RESULT_PROCESSING_MODE' => [
                'RENAME' => 'LIST_RESULT_PROCESSING_MODE',
                'MOVE' => 'LIST'
            ],
            'SORT_BY_1' => [
                'MOVE' => 'LIST'
            ],
            'SORT_ORDER_1' => [
                'MOVE' => 'LIST'
            ],
            'SORT_BY_2' => [
                'MOVE' => 'LIST'
            ],
            'SORT_ORDER_2' => [
                'MOVE' => 'LIST'
            ],
            'DATE_FORMAT' => [
                'RENAME' => 'LIST_DATE_FORMAT',
                'MOVE' => 'LIST'
            ]
        ],
        $arCurrentValues
    );

    $paramsElementsDetail = ComponentParameters::getParameters(
        $currentZone.':elements.detail',
        [
            'ELEMENT_ID' => [
                'DELETE' => true
            ],
            'ELEMENT_CODE' => [
                'DELETE' => true
            ],
            'SELECT_FIELDS' => [
                'RENAME' => 'DETAIL_SELECT_FIELDS',
                'MOVE' => 'DETAIL'
            ],
            'SELECT_PROPS' => [
                'RENAME' => 'DETAIL_SELECT_PROPS',
                'MOVE' => 'DETAIL'
            ],
            'RESULT_PROCESSING_MODE' => [
                'RENAME' => 'DETAIL_RESULT_PROCESSING_MODE',
                'MOVE' => 'DETAIL'
            ],
            'DATE_FORMAT' => [
                'RENAME' => 'DETAIL_DATE_FORMAT',
                'MOVE' => 'DETAIL'
            ],
            'OG_TAGS_TITLE' => [
                'RENAME' => 'DETAIL_OG_TAGS_TITLE'
            ],
            'OG_TAGS_DESCRIPTION' => [
                'RENAME' => 'DETAIL_OG_TAGS_DESCRIPTION'
            ],
            'OG_TAGS_IMAGE' => [
                'RENAME' => 'DETAIL_OG_TAGS_IMAGE'
            ],
            'OG_TAGS_URL' => [
                'RENAME' => 'DETAIL_OG_TAGS_URL'
            ]
        ],
        $arCurrentValues
    );

    $arComponentParameters = array_replace_recursive($currentParameters, $paramsElementsList, $paramsElementsDetail);
}
catch (\Exception $e)
{
    ShowError($e->getMessage());
}