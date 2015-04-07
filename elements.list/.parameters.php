<?php

use Bex\Bbc\Helpers\ComponentParameters;
use Bitrix\Iblock;
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

if (!\Bitrix\Main\Loader::includeModule('bex.bbc')) return false;

Loc::loadMessages(__FILE__);

try
{
    ComponentParameters::includeModules(['iblock']);

    $iblockTypes = CIBlockParameters::GetIBlockTypes([0 => '']);
    $iblocks = [0 => ''];
    $sections = [0 => ''];
    $elementProperties = [];

    if (isset($arCurrentValues['IBLOCK_TYPE']) && strlen($arCurrentValues['IBLOCK_TYPE']))
    {
        $rsIblocks = Iblock\IblockTable::getList([
            'order' => [
                'SORT' => 'ASC',
                'NAME' => 'ASC'
            ],
            'filter' => [
                'IBLOCK_TYPE_ID' => $arCurrentValues['IBLOCK_TYPE'],
                'ACTIVE' => 'Y'
            ],
            'select' => [
                'ID',
                'NAME'
            ]
        ]);

        while ($iblock = $rsIblocks->fetch())
        {
            $iblocks[$iblock['ID']] = $iblock['NAME'];
        }
    }

    if (isset($arCurrentValues['IBLOCK_ID']) && strlen($arCurrentValues['IBLOCK_ID']))
    {
        $rsSections = Iblock\SectionTable::getList([
            'order' => [
                'SORT' => 'ASC',
                'NAME' => 'ASC'
            ],
            'filter' => [
                'IBLOCK_ID' => $arCurrentValues['IBLOCK_ID'],
                'ACTIVE' => 'Y'
            ],
            'select' => [
                'ID',
                'NAME'
            ]
        ]);

        while ($arSection = $rsSections->fetch())
        {
            $sections[$arSection['ID']] = $arSection['NAME'];
        }

        $rsProperties = CIBlockProperty::GetList(
            [
                'sort' => 'asc',
                'name' => 'asc'
            ],
            [
                'ACTIVE' => 'Y',
                'IBLOCK_ID' => $arCurrentValues['IBLOCK_ID']
            ]
        );

        while ($property = $rsProperties->Fetch())
        {
            $elementProperties[$property['CODE']] = '['.$property['CODE'].'] '.$property['NAME'];
        }
    }

    $paramElementsFields = CIBlockParameters::GetFieldCode(Loc::getMessage('ELEMENTS_LIST_FIELDS'), 'BASE');

    $sortOrders = [
        'ASC' => Loc::getMessage('ELEMENTS_LIST_SORT_ORDER_ASC'),
        'DESC' => Loc::getMessage('ELEMENTS_LIST_SORT_ORDER_DESC')
    ];

    $arComponentParameters = [
        'GROUPS' => [
            'AJAX' => [
                'NAME' => Loc::getMessage('ELEMENTS_LIST_GROUP_AJAX')
            ],
            'SEO' => [
                'NAME' => Loc::getMessage('ELEMENTS_LIST_GROUP_SEO')
            ],
            'OTHERS' => [
                'NAME' => Loc::getMessage('ELEMENTS_LIST_GROUP_OTHERS')
            ]
        ],
        'PARAMETERS' => [
            'IBLOCK_TYPE' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_IBLOCK_TYPE'),
                'TYPE' => 'LIST',
                'VALUES' => $iblockTypes,
                'DEFAULT' => '',
                'REFRESH' => 'Y'
            ],
            'IBLOCK_ID' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_IBLOCK_ID'),
                'TYPE' => 'LIST',
                'VALUES' => $iblocks,
                'REFRESH' => 'Y'
            ],
            'SECTION_ID' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SECTION_ID'),
                'TYPE' => 'LIST',
                'VALUES' => $sections
            ],
            'SECTION_CODE' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SECTION_CODE'),
                'TYPE' => 'STRING'
            ],
            'INCLUDE_SUBSECTIONS' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_INCLUDE_SUBSECTIONS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ],
            'SORT_BY_1' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SORT_BY_1'),
                'TYPE' => 'LIST',
                'VALUES' => CIBlockParameters::GetElementSortFields()
            ],
            'SORT_ORDER_1' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SORT_ORDER_1'),
                'TYPE' => 'LIST',
                'VALUES' => $sortOrders
            ],
            'SORT_BY_2' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SORT_BY_2'),
                'TYPE' => 'LIST',
                'VALUES' => CIBlockParameters::GetElementSortFields()
            ],
            'SORT_ORDER_2' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SORT_ORDER_2'),
                'TYPE' => 'LIST',
                'VALUES' => $sortOrders
            ],
            'SELECT_FIELDS' => $paramElementsFields,
            'SELECT_PROPS' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PROPERTIES'),
                'TYPE' => 'LIST',
                'MULTIPLE' => 'Y',
                'VALUES' => $elementProperties,
                'ADDITIONAL_VALUES' => 'Y'
            ],
            'RESULT_PROCESSING_MODE' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_RESULT_PROCESSING_MODE'),
                'TYPE' => 'LIST',
                'VALUES' => [
                    'DEFAULT' => Loc::getMessage('ELEMENTS_LIST_RESULT_PROCESSING_MODE_DEFAULT'),
                    'EXTENDED' => Loc::getMessage('ELEMENTS_LIST_RESULT_PROCESSING_MODE_EXTENDED')
                ]
            ],
            'EX_FILTER_NAME' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_EX_FILTER_NAME'),
                'TYPE' => 'STRING',
                'DEFAULT' => ''
            ],
            'PAGER_SAVE_SESSION' => [
                'PARENT' => 'PAGER_SETTINGS',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_NAV_SAVE_SESSION'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ],
            'ELEMENTS_COUNT' => [
                'PARENT' => 'PAGER_SETTINGS',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_ELEMENTS_COUNT'),
                'TYPE' => 'STRING',
                'DEFAULT' => '10'
            ],
            'USE_AJAX' => [
                'PARENT' => 'AJAX',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_USE_AJAX'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y'
            ],
            'AJAX_TYPE' => [
                'PARENT' => 'AJAX',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_AJAX_TYPE'),
                'TYPE' => 'LIST',
                'VALUES' => [
                    'DEFAULT' => Loc::getMessage('ELEMENTS_LIST_AJAX_TYPE_DEFAULT'),
                    'JSON' => Loc::getMessage('ELEMENTS_LIST_AJAX_TYPE_JSON')
                ]
            ],
            'AJAX_HEAD_RELOAD' => [
                'PARENT' => 'AJAX',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_AJAX_HEAD_RELOAD'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ],
            'AJAX_TEMPLATE_PAGE' => [
                'PARENT' => 'AJAX',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_AJAX_TEMPLATE_PAGE'),
                'TYPE' => 'STRING',
                'DEFAULT' => ''
            ],
            'AJAX_COMPONENT_ID' => [
                'PARENT' => 'AJAX',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_AJAX_COMPONENT_ID'),
                'TYPE' => 'STRING',
                'DEFAULT' => ''
            ],
            'SET_SEO_TAGS' => [
                'PARENT' => 'SEO',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SET_SEO_TAGS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y'
            ],
            'ADD_SECTIONS_CHAIN' => [
                'PARENT' => 'SEO',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_ADD_SECTIONS_CHAIN'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y'
            ],
            'SET_404' => [
                'PARENT' => 'OTHERS',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SET_404'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ],
            'CHECK_PERMISSIONS' => [
                'PARENT' => 'OTHERS',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_CHECK_PERMISSIONS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y'
            ],
            'DATE_FORMAT' => CIBlockParameters::GetDateFormat(
                Loc::getMessage('ELEMENTS_LIST_DATE_FORMAT'),
                'OTHERS'
            ),
            'CACHE_GROUPS' => [
                'PARENT' => 'CACHE_SETTINGS',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_CACHE_GROUPS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ],
            'CACHE_TIME' => [
                'DEFAULT' => 360000
            ]
        ]
    ];

    CIBlockParameters::AddPagerSettings($arComponentParameters, Loc::getMessage('ELEMENTS_LIST_NAV_TITLE'), true, true);
}
catch (Exception $e)
{
    ShowError($e->getMessage());
}