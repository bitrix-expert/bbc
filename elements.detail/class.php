<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Components;

use Bex\Bbc;

if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

if (!\Bitrix\Main\Loader::includeModule('bex.bbc')) return false;

/**
 * Show page with element of the info-block
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class ElementsDetail extends Bbc\Basis
{
    use Bbc\Traits\Elements;

    protected $needModules = array('iblock');

    protected $checkParams = array(
        'IBLOCK_TYPE' => array('type' => 'string'),
        'IBLOCK_ID' => array('type' => 'int'),
        'ELEMENT_ID' => array('type' => 'int', 'error' => false),
        'ELEMENT_CODE' => array('type' => 'string', 'error' => false)
    );

    protected function executeProlog()
    {
        if (!$this->arParams['ELEMENT_ID'] && !$this->arParams['ELEMENT_CODE'])
        {
            $this->return404(true);
        }
    }

    protected function executeMain()
    {
        $rsElement = \CIBlockElement::GetList(
            array(),
            $this->getParamsFilters(),
            false,
            false,
            $this->getParamsSelected()
        );

        $processingMethod = $this->getProcessingMethod();

        if ($element = $rsElement->$processingMethod())
        {
            if ($arElement = $this->processingElementsResult($element))
            {
                $this->arResult = array_merge($this->arResult, $arElement);
            }

            $this->setResultCacheKeys(array(
                'ID',
                'IBLOCK_ID',
                'CODE',
                'NAME',
                'IBLOCK_SECTION_ID',
                'IBLOCK',
                'LIST_PAGE_URL',
                'SECTION_URL',
                'SECTION'
            ));
        }
        elseif ($this->arParams['SET_404'] === 'Y')
        {
            $this->return404();
        }
    }
}