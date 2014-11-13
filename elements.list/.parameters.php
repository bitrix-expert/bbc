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

if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

use Bitrix\Iblock;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

\CBitrixComponent::includeComponentClass(basename(dirname(__DIR__)).':basis');


try
{
    Common::includeModules(array('iblock'));

    $iblockTypes = \CIBlockParameters::GetIBlockTypes(array(0 => ''));
    $iblocks = array();
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

    $paramElementsFields = \CIBlockParameters::GetFieldCode(Loc::getMessage('ELEMENTS_LIST_PARAMETERS_FIELDS'), 'BASE');

    $sortOrders = array(
        'ASC' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_SORT_ORDER_ASC'),
        'DESC' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_SORT_ORDER_DESC')
    );

    $arComponentParameters = array(
        'GROUPS' => array(
            'OTHERS' => array(
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_GROUP_OTHERS')
            ),
            'SEO' => array(
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_GROUP_SEO')
            ),
        ),
        'PARAMETERS' => array(
            'IBLOCK_TYPE' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_IBLOCK_TYPE'),
                'TYPE' => 'LIST',
                'VALUES' => $iblockTypes,
                'DEFAULT' => '',
                'REFRESH' => 'Y'
            ),
            'IBLOCK_ID' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_IBLOCK_ID'),
                'TYPE' => 'LIST',
                'VALUES' => $iblocks
            ),
            'SECTION_ID' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_SECTION_ID'),
                'TYPE' => 'LIST',
                'VALUES' => $sections
            ),
            'SORT_BY_1' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_SORT_BY_1'),
                'TYPE' => 'LIST',
                'VALUES' => \CIBlockParameters::GetElementSortFields()
            ),
            'SORT_ORDER_1' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_SORT_ORDER_1'),
                'TYPE' => 'LIST',
                'VALUES' => $sortOrders
            ),
            'SORT_BY_2' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_SORT_BY_2'),
                'TYPE' => 'LIST',
                'VALUES' => \CIBlockParameters::GetElementSortFields()
            ),
            'SORT_ORDER_2' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_SORT_ORDER_2'),
                'TYPE' => 'LIST',
                'VALUES' => $sortOrders
            ),
            'SELECT_FIELDS' => $paramElementsFields,
            'SELECT_PROPS' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_PROPERTIES'),
                'TYPE' => 'LIST',
                'MULTIPLE' => 'Y',
                'VALUES' => $elementProperties,
                'ADDITIONAL_VALUES' => 'Y'
            ),
            'PAGER_SAVE_SESSION' => array(
                'PARENT' => 'PAGER_SETTINGS',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_NAV_SAVE_SESSION'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ),
            'ELEMENTS_COUNT' => array(
                'PARENT' => 'PAGER_SETTINGS',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_ELEMENTS_COUNT'),
                'TYPE' => 'STRING',
                'DEFAULT' => '10'
            ),
            'SET_SEO_TAGS' => array(
                'PARENT' => 'SEO',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_SET_SEO_TAGS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y'
            ),
            'SET_404' => array(
                'PARENT' => 'OTHERS',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_SET_404'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ),
            'DATE_FORMAT' => \CIBlockParameters::GetDateFormat(
                Loc::getMessage('ELEMENTS_LIST_PARAMETERS_DATE_FORMAT'),
                'OTHERS'
            ),
            'CACHE_GROUPS' => array(
                'PARENT' => 'CACHE_SETTINGS',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_CACHE_GROUPS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ),
            'CACHE_TIME' => array(
                'DEFAULT' => 360000
            )
        )
    );

    \CIBlockParameters::AddPagerSettings($arComponentParameters, Loc::getMessage('ELEMENTS_LIST_PARAMETERS_NAV_TITLE'), true, true);
}
catch (\Exception $e)
{
    ShowError($e->getMessage());
}
