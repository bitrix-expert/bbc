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

use Bitrix\Iblock;
use Bitrix\Main\Localization\Loc;


if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

Loc::loadMessages(__FILE__);

\CBitrixComponent::includeComponentClass(basename(dirname(__DIR__)).':basis');


try
{
    ComponentHelpers::includeModules(array('iblock'));

    $iblockTypes = \CIBlockParameters::GetIBlockTypes(array(0 => ''));
    $iblocks = array();
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

    $paramElementsFields = \CIBlockParameters::GetFieldCode(Loc::getMessage('ELEMENTS_DETAIL_FIELDS'), 'BASE');

    $arComponentParameters = array(
        'GROUPS' => array(
            'OTHERS' => array(
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_GROUP_OTHERS')
            ),
            'SEO' => array(
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_GROUP_SEO')
            ),
        ),
        'PARAMETERS' => array(
            'IBLOCK_TYPE' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_IBLOCK_TYPE'),
                'TYPE' => 'LIST',
                'VALUES' => $iblockTypes,
                'DEFAULT' => '',
                'REFRESH' => 'Y'
            ),
            'IBLOCK_ID' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_IBLOCK_ID'),
                'TYPE' => 'LIST',
                'VALUES' => $iblocks
            ),
            'ELEMENT_ID' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_ELEMENT_ID'),
                'TYPE' => 'string'
            ),
            'ELEMENT_CODE' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_ELEMENT_CODE'),
                'TYPE' => 'string'
            ),
            'SELECT_FIELDS' => $paramElementsFields,
            'SELECT_PROPS' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_PROPERTIES'),
                'TYPE' => 'LIST',
                'MULTIPLE' => 'Y',
                'VALUES' => $elementProperties,
                'ADDITIONAL_VALUES' => 'Y'
            ),
            'RESULT_PROCESSING_MODE' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_RESULT_PROCESSING_MODE'),
                'TYPE' => 'LIST',
                'VALUES' => array(
                    'DEFAULT' => Loc::getMessage('ELEMENTS_DETAIL_RESULT_PROCESSING_MODE_DEFAULT'),
                    'EXTENDED' => Loc::getMessage('ELEMENTS_DETAIL_RESULT_PROCESSING_MODE_EXTENDED')
                )
            ),
            'SET_SEO_TAGS' => array(
                'PARENT' => 'SEO',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_SET_SEO_TAGS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'Y'
            ),
            'SET_404' => array(
                'PARENT' => 'OTHERS',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_SET_404'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ),
            'DATE_FORMAT' => \CIBlockParameters::GetDateFormat(
                Loc::getMessage('ELEMENTS_DETAIL_DATE_FORMAT'),
                'OTHERS'
            ),
            'CACHE_GROUPS' => array(
                'PARENT' => 'CACHE_SETTINGS',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_CACHE_GROUPS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ),
            'CACHE_TIME' => array(
                'DEFAULT' => 360000
            )
        )
    );
}
catch (\Exception $e)
{
    ShowError($e->getMessage());
}