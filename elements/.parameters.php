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

namespace Components\Basis;

use Bitrix\Iblock;
use Bitrix\Main\Localization\Loc;


if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

Loc::loadMessages(__FILE__);

$currentZone = basename(dirname(__DIR__));
\CBitrixComponent::includeComponentClass($currentZone.':basis');


/**
 * @global array $arCurrentValues
 */
try
{
    $currentParameters = array(
        'GROUPS' => array(
            'LIST' => array(
                'NAME' => Loc::getMessage('ELEMENTS_GROUP_LIST'),
                'SORT' => '200'
            ),
            'DETAIL' => array(
                'NAME' => Loc::getMessage('ELEMENTS_GROUP_DETAIL'),
                'SORT' => '300'
            )
        ),
        'PARAMETERS' => array(
            'SEF_MODE' => array(
                'index' => array(
                    'NAME' => Loc::getMessage('ELEMENTS_SEF_INDEX'),
                    'DEFAULT' => '',
                    'VARIABLES' => array()
                ),
                'section' => array(
                    'NAME' => Loc::getMessage('ELEMENTS_SEF_SECTION'),
                    'DEFAULT' => '#SECTION_CODE#/',
                    'VARIABLES' => array('SECTION_CODE')
                ),
                'detail' => array(
                    'NAME' => Loc::getMessage('ELEMENTS_SEF_DETAIL'),
                    'DEFAULT' => '#SECTION_CODE#/#ELEMENT_CODE#/',
                    'VARIABLES' => array('ELEMENT_CODE', 'SECTION_CODE')
                )
            ),
            'USE_SEARCH' => array(
                'PARENT' => 'OTHERS',
                'NAME' => Loc::getMessage('ELEMENTS_DETAIL_PARAMETERS_USE_SEARCH'),
                'TYPE' => 'CHECKBOX',
                'DEFAULT' => 'N'
            )
        )
    );

    $paramsElementsList = ComponentHelpers::getParameters(
        $currentZone.':elements.list',
        array(
            'SECTION_ID' => array(
                'DELETE' => true
            ),
            'SECTION_CODE' => array(
                'DELETE' => true
            ),
            'SELECT_FIELDS' => array(
                'RENAME' => 'LIST_SELECT_FIELDS',
                'MOVE' => 'LIST'
            ),
            'SELECT_PROPS' => array(
                'RENAME' => 'LIST_SELECT_PROPS',
                'MOVE' => 'LIST'
            ),
            'RESULT_PROCESSING_MODE' => array(
                'RENAME' => 'LIST_RESULT_PROCESSING_MODE',
                'MOVE' => 'LIST'
            ),
            'SORT_BY_1' => array(
                'MOVE' => 'LIST'
            ),
            'SORT_ORDER_1' => array(
                'MOVE' => 'LIST'
            ),
            'SORT_BY_2' => array(
                'MOVE' => 'LIST'
            ),
            'SORT_ORDER_2' => array(
                'MOVE' => 'LIST'
            ),
            'DATE_FORMAT' => array(
                'RENAME' => 'LIST_DATE_FORMAT',
                'MOVE' => 'LIST'
            )
        ),
        $arCurrentValues
    );

    $paramsElementsDetail = ComponentHelpers::getParameters(
        $currentZone.':elements.detail',
        array(
            'ELEMENT_ID' => array(
                'DELETE' => true
            ),
            'ELEMENT_CODE' => array(
                'DELETE' => true
            ),
            'SELECT_FIELDS' => array(
                'RENAME' => 'DETAIL_SELECT_FIELDS',
                'MOVE' => 'DETAIL'
            ),
            'SELECT_PROPS' => array(
                'RENAME' => 'DETAIL_SELECT_PROPS',
                'MOVE' => 'DETAIL'
            ),
            'RESULT_PROCESSING_MODE' => array(
                'RENAME' => 'DETAIL_RESULT_PROCESSING_MODE',
                'MOVE' => 'DETAIL'
            ),
            'DATE_FORMAT' => array(
                'RENAME' => 'DETAIL_DATE_FORMAT',
                'MOVE' => 'DETAIL'
            ),
            'OG_TAGS_TITLE' => array(
                'RENAME' => 'DETAIL_OG_TAGS_TITLE'
            ),
            'OG_TAGS_DESCRIPTION' => array(
                'RENAME' => 'DETAIL_OG_TAGS_DESCRIPTION'
            ),
            'OG_TAGS_IMAGE' => array(
                'RENAME' => 'DETAIL_OG_TAGS_IMAGE'
            ),
            'OG_TAGS_URL' => array(
                'RENAME' => 'DETAIL_OG_TAGS_URL'
            )
        ),
        $arCurrentValues
    );

    $arComponentParameters = array_replace_recursive($currentParameters, $paramsElementsList, $paramsElementsDetail);
}
catch (\Exception $e)
{
    ShowError($e->getMessage());
}