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
    $iblocks = [];
    $elementProperties = [];

    $ogTagsFields = [
        'TITLE' => [
            0 => '',
            'NAME' => Loc::getMessage('ELEMENTS_DETAIL_ELEMENT_NAME'),
            'SEO_TITLE' => Loc::getMessage('ELEMENTS_DETAIL_PAGE_TITLE')
        ],
        'DESCRIPTION' => [
            0 => '',
            'PREVIEW_TEXT' => Loc::getMessage('ELEMENTS_DETAIL_PREVIEW_TEXT'),
            'DETAIL_TEXT' => Loc::getMessage('ELEMENTS_DETAIL_DETAIL_TEXT'),
            'SEO_DESCRIPTION' => Loc::getMessage('ELEMENTS_DETAIL_PAGE_DESCRIPTION')
        ],
        'IMAGE' => [
            0 => '',
            'PREVIEW_PICTURE' => Loc::getMessage('ELEMENTS_DETAIL_PREVIEW_PICTURE'),
            'DETAIL_PICTURE' => Loc::getMessage('ELEMENTS_DETAIL_DETAIIL_PICTURE')
        ],
        'URL' => [
            0 => '',
            'SHORT_LINK' => Loc::getMessage('ELEMENTS_DETAIL_SHORT_LINK')
        ]
    ];

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

            if ($property['PROPERTY_TYPE'] === 'S')
            {
                $ogTagsFields['TITLE']['PROPERTY_'.$property['CODE']] = $property['NAME'];
                $ogTagsFields['DESCRIPTION']['PROPERTY_'.$property['CODE']] = $property['NAME'];
            }

            if ($property['PROPERTY_TYPE'] === 'F')
            {
//                $ogTagsFields['IMAGE']['PROPERTY_'.$property['CODE']] = $property['NAME']; // todo Add prepare to Elements::executePrologElements()
            }
        }
    }

    $paramElementsFields = CIBlockParameters::GetFieldCode(Loc::getMessage('ELEMENTS_DETAIL_FIELDS'), 'BASE');

    $arComponentParameters = [
        'GROUPS' => [
            'OTHERS' => [
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_GROUP_OTHERS')
            ],
            'SEO' => [
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_GROUP_SEO')
            ],
        ],
        'PARAMETERS' => [
            'IBLOCK_TYPE' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_IBLOCK_TYPE'),
                'TYPE' => 'LIST',
                'VALUES' => $iblockTypes,
                'DEFAULT' => '',
                'REFRESH' => 'Y'
            ],
            'IBLOCK_ID' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_IBLOCK_ID'),
                'TYPE' => 'LIST',
                'VALUES' => $iblocks
            ],
            'ELEMENT_ID' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_ELEMENT_ID'),
                'TYPE' => 'string'
            ],
            'ELEMENT_CODE' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_ELEMENT_CODE'),
                'TYPE' => 'string'
            ],
            'SELECT_FIELDS' => $paramElementsFields,
            'SELECT_PROPS' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_PROPERTIES'),
                'TYPE' => 'LIST',
                'MULTIPLE' => 'Y',
                'VALUES' => $elementProperties,
                'ADDITIONAL_VALUES' => 'Y'
            ],
            'RESULT_PROCESSING_MODE' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_RESULT_PROCESSING_MODE'),
                'TYPE' => 'LIST',
                'VALUES' => [
                    'DEFAULT' => Loc::getMessage('ELEMENTS_DETAIL_RESULT_PROCESSING_MODE_DEFAULT'),
                    'EXTENDED' => Loc::getMessage('ELEMENTS_DETAIL_RESULT_PROCESSING_MODE_EXTENDED')
                ]
            ],
            'SET_SEO_TAGS' => [
                'PARENT' => 'SEO',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_SET_SEO_TAGS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y'
            ],
            'OG_TAGS_TITLE' => [
                'PARENT' => 'SEO',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_OG_TAGS_TITLE'),
                'TYPE' => 'LIST',
                'VALUES' => $ogTagsFields['TITLE']
            ],
            'OG_TAGS_DESCRIPTION' => [
                'PARENT' => 'SEO',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_OG_TAGS_DESCRIPTION'),
                'TYPE' => 'LIST',
                'VALUES' => $ogTagsFields['DESCRIPTION']
            ],
            'OG_TAGS_IMAGE' => [
                'PARENT' => 'SEO',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_OG_TAGS_IMAGE'),
                'TYPE' => 'LIST',
                'VALUES' => $ogTagsFields['IMAGE']
            ],
            'OG_TAGS_URL' => [
                'PARENT' => 'SEO',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_OG_TAGS_URL'),
                'TYPE' => 'LIST',
                'VALUES' => $ogTagsFields['URL']
            ],
            'ADD_SECTIONS_CHAIN' => [
                'PARENT' => 'SEO',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_ADD_SECTIONS_CHAIN'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y'
            ],
            'ADD_ELEMENT_CHAIN' => [
                'PARENT' => 'SEO',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_ADD_ELEMENT_CHAIN'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y'
            ],
            'SET_404' => [
                'PARENT' => 'OTHERS',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_SET_404'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ],
            'DATE_FORMAT' => CIBlockParameters::GetDateFormat(
                Loc::getMessage('ELEMENTS_DETAIL_DATE_FORMAT'),
                'OTHERS'
            ),
            'CACHE_GROUPS' => [
                'PARENT' => 'CACHE_SETTINGS',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_CACHE_GROUPS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ],
            'CACHE_TIME' => [
                'DEFAULT' => 360000
            ]
        ]
    ];
}
catch (Exception $e)
{
    ShowError($e->getMessage());
}