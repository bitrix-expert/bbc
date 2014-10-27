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

    $arComponentParameters = array(
        'GROUPS' => array(),
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
            'NAV_SHOW' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_NAV_SHOW'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ),
            'NAV_SHOW_ALWAYS' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_NAV_SHOW_ALWAYS'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            ),
            'NAV_TITLE' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_NAV_TITLE'),
                'TYPE' => 'STRING',
                'DEFAULT' => ''
            ),
            'ELEMENTS_COUNT' => array(
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('ELEMENTS_LIST_PARAMETERS_ELEMENTS_COUNT'),
                'TYPE' => 'STRING',
                'DEFAULT' => ''
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