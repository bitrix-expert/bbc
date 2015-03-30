<?php
/**
 * Basis components
 *
 * @package components
 * @subpackage basis
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Expert\Bbc\Components;

use Bitrix\Iblock;
use Bitrix\Main\Localization\Loc;


if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

Loc::loadMessages(__FILE__);

\CBitrixComponent::includeComponentClass(basename(dirname(__DIR__)).':basis');


try
{
    ComponentHelpers::includeModules(array('iblock'));

    $iblockTypes = \CIBlockParameters::GetIBlockTypes(array(0 => ''));
    $iblocks = array(0 => '');
    $sections = array(0 => '');
    $elementProperties = array();

    if (isset($arCurrentValues['IBLOCK_TYPE']) && strlen($arCurrentValues['IBLOCK_TYPE']))
    {
        $rsIblocks = Iblock\IblockTable::getList(array(
            'order' => array(
                'SORT' => 'ASC',
                'NAME' => 'ASC'
            ),
            'filter' => array(
                'IBLOCK_TYPE_ID' => $arCurrentValues['IBLOCK_TYPE'],
                'ACTIVE' => 'Y'
            ),
            'select' => array(
                'ID',
                'NAME'
            )
        ));

        while ($arIBlock = $rsIblocks->fetch())
        {
            $iblocks[$arIBlock['ID']] = $arIBlock['NAME'];
        }
    }

    if (isset($arCurrentValues['IBLOCK_ID']) && strlen($arCurrentValues['IBLOCK_ID']))
    {
        $rsSections = Iblock\SectionTable::getList(array(
            'order' => array(
                'SORT' => 'ASC',
                'NAME' => 'ASC'
            ),
            'filter' => array(
                'IBLOCK_ID' => $arCurrentValues['IBLOCK_ID'],
                'ACTIVE' => 'Y'
            ),
            'select' => array(
                'ID',
                'NAME'
            )
        ));

        while ($arSection = $rsSections->fetch())
        {
            $sections[$arSection['ID']] = $arSection['NAME'];
        }

        $rsProperties = \CIBlockProperty::GetList(
            array(
                'sort' => 'asc',
                'name' => 'asc'
            ),
            array(
                'ACTIVE' => 'Y',
                'IBLOCK_ID' => $arCurrentValues['IBLOCK_ID']
            )
        );

        while ($arProperty = $rsProperties->Fetch())
        {
            $elementProperties[$arProperty['CODE']] = '['.$arProperty['CODE'].'] '.$arProperty['NAME'];
        }
    }

    $paramElementsFields = \CIBlockParameters::GetFieldCode(Loc::getMessage('ELEMENTS_LIST_FIELDS'), 'BASE');

    $sortOrders = array(
        'ASC' => Loc::getMessage('ELEMENTS_LIST_SORT_ORDER_ASC'),
        'DESC' => Loc::getMessage('ELEMENTS_LIST_SORT_ORDER_DESC')
    );

    $arComponentParameters = array(
        'GROUPS' => array(
            'AJAX' => array(
                'NAME' => Loc::getMessage('ELEMENTS_LIST_GROUP_AJAX')
            ),
            'SEO' => array(
                'NAME' => Loc::getMessage('ELEMENTS_LIST_GROUP_SEO')
            ),
            'OTHERS' => array(
                'NAME' => Loc::getMessage('ELEMENTS_LIST_GROUP_OTHERS')
            )
        ),
        'PARAMETERS' => array(
            'IBLOCK_TYPE' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_IBLOCK_TYPE'),
                'TYPE' => 'LIST',
                'VALUES' => $iblockTypes,
                'DEFAULT' => '',
                'REFRESH' => 'Y'
            ),
            'IBLOCK_ID' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_IBLOCK_ID'),
                'TYPE' => 'LIST',
                'VALUES' => $iblocks,
                'REFRESH' => 'Y'
            ),
            'SECTION_ID' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SECTION_ID'),
                'TYPE' => 'LIST',
                'VALUES' => $sections
            ),
            'SECTION_CODE' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SECTION_CODE'),
                'TYPE' => 'STRING'
            ),
            'INCLUDE_SUBSECTIONS' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_INCLUDE_SUBSECTIONS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ),
            'SORT_BY_1' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SORT_BY_1'),
                'TYPE' => 'LIST',
                'VALUES' => \CIBlockParameters::GetElementSortFields()
            ),
            'SORT_ORDER_1' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SORT_ORDER_1'),
                'TYPE' => 'LIST',
                'VALUES' => $sortOrders
            ),
            'SORT_BY_2' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SORT_BY_2'),
                'TYPE' => 'LIST',
                'VALUES' => \CIBlockParameters::GetElementSortFields()
            ),
            'SORT_ORDER_2' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SORT_ORDER_2'),
                'TYPE' => 'LIST',
                'VALUES' => $sortOrders
            ),
            'SELECT_FIELDS' => $paramElementsFields,
            'SELECT_PROPS' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PROPERTIES'),
                'TYPE' => 'LIST',
                'MULTIPLE' => 'Y',
                'VALUES' => $elementProperties,
                'ADDITIONAL_VALUES' => 'Y'
            ),
            'RESULT_PROCESSING_MODE' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_RESULT_PROCESSING_MODE'),
                'TYPE' => 'LIST',
                'VALUES' => array(
                    'DEFAULT' => Loc::getMessage('ELEMENTS_LIST_RESULT_PROCESSING_MODE_DEFAULT'),
                    'EXTENDED' => Loc::getMessage('ELEMENTS_LIST_RESULT_PROCESSING_MODE_EXTENDED')
                )
            ),
            'EX_FILTER_NAME' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_EX_FILTER_NAME'),
                'TYPE' => 'STRING',
                'DEFAULT' => ''
            ),
            'PAGER_SAVE_SESSION' => array(
                'PARENT' => 'PAGER_SETTINGS',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_NAV_SAVE_SESSION'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ),
            'ELEMENTS_COUNT' => array(
                'PARENT' => 'PAGER_SETTINGS',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_ELEMENTS_COUNT'),
                'TYPE' => 'STRING',
                'DEFAULT' => '10'
            ),
            'USE_AJAX' => array(
                'PARENT' => 'AJAX',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_USE_AJAX'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y'
            ),
            'AJAX_TYPE' => array(
                'PARENT' => 'AJAX',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_AJAX_TYPE'),
                'TYPE' => 'LIST',
                'VALUES' => array(
                    'DEFAULT' => Loc::getMessage('ELEMENTS_LIST_AJAX_TYPE_DEFAULT'),
                    'JSON' => Loc::getMessage('ELEMENTS_LIST_AJAX_TYPE_JSON')
                )
            ),
            'AJAX_HEAD_RELOAD' => array(
                'PARENT' => 'AJAX',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_AJAX_HEAD_RELOAD'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ),
            'AJAX_TEMPLATE_PAGE' => array(
                'PARENT' => 'AJAX',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_AJAX_TEMPLATE_PAGE'),
                'TYPE' => 'STRING',
                'DEFAULT' => ''
            ),
            'AJAX_COMPONENT_ID' => array(
                'PARENT' => 'AJAX',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_AJAX_COMPONENT_ID'),
                'TYPE' => 'STRING',
                'DEFAULT' => ''
            ),
            'SET_SEO_TAGS' => array(
                'PARENT' => 'SEO',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SET_SEO_TAGS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y'
            ),
            'ADD_SECTIONS_CHAIN' => array(
                'PARENT' => 'SEO',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_ADD_SECTIONS_CHAIN'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y'
            ),
            'SET_404' => array(
                'PARENT' => 'OTHERS',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_SET_404'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ),
            'CHECK_PERMISSIONS' => array(
                'PARENT' => 'OTHERS',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_CHECK_PERMISSIONS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y'
            ),
            'DATE_FORMAT' => \CIBlockParameters::GetDateFormat(
                Loc::getMessage('ELEMENTS_LIST_DATE_FORMAT'),
                'OTHERS'
            ),
            'CACHE_GROUPS' => array(
                'PARENT' => 'CACHE_SETTINGS',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_CACHE_GROUPS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ),
            'CACHE_TIME' => array(
                'DEFAULT' => 360000
            )
        )
    );

    \CIBlockParameters::AddPagerSettings($arComponentParameters, Loc::getMessage('ELEMENTS_LIST_NAV_TITLE'), true, true);
}
catch (\Exception $e)
{
    ShowError($e->getMessage());
}