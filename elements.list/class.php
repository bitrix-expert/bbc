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

namespace Bex\Bbc\Components;

use Bex\Bbc;


if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

if (!\Bitrix\Main\Loader::includeModule('bex.bbc')) return false;


/**
 * Component for show elements list
 */
class ElementsList extends Bbc\Basis
{
    use Bbc\Traits\Elements;

    protected $needModules = array('iblock');

    protected $checkParams = array(
        'IBLOCK_TYPE' => array('type' => 'string'),
        'IBLOCK_ID' => array('type' => 'int')
    );

    protected function executeMain()
    {
        $rsElements = \CIBlockElement::GetList(
            $this->getParamsSort(),
            $this->getParamsFilters(),
            $this->getParamsGrouping(),
            $this->getParamsNavStart(),
            $this->getParamsSelected(array(
                'DETAIL_PAGE_URL',
                'LIST_PAGE_URL'
            ))
        );

        if (!isset($this->arResult['ELEMENTS']))
        {
        	$this->arResult['ELEMENTS'] = array();
        }

        $processingMethod = $this->getProcessingMethod();

        while ($element = $rsElements->$processingMethod())
        {
            if ($arElement = $this->processingElementsResult($element))
            {
                $this->arResult['ELEMENTS'][] = $arElement;
            }
        }

        if ($this->arParams['SET_404'] === 'Y' && empty($this->arResult['ELEMENTS']))
        {
            $this->return404();
        }

        $this->generateNav($rsElements);
        $this->setResultCacheKeys(array('NAV_CACHED_DATA'));
    }
}