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

\CBitrixComponent::includeComponentClass(basename(dirname(__DIR__)).':basis');


/**
 * Component for show elements list
 */
class ElementsList extends Basis
{
    use Elements;

    protected $needModules = array('iblock');

    protected $checkParams = array(
        'IBLOCK_TYPE' => array('type' => 'string'),
        'IBLOCK_ID' => array('type' => 'int')
    );

    protected function getResult()
    {
        $rsElements = \CIBlockElement::GetList(
            $this->getParamsSort(),
            $this->getParamsFilters(),
            false,
            $this->getParamsNavStart(),
            $this->getParamsSelected(array(
                'DETAIL_PAGE_URL',
                'LIST_PAGE_URL'
            ))
        );

        if ($this->arParams['RESULT_PROCESSING_MODE'] === 'Y')
        {
            $processingMethod = 'GetNextElement';
        }
        else
        {
            $processingMethod = 'GetNext';
        }

        while ($element = $rsElements->$processingMethod())
        {
            if ($this->arParams['RESULT_PROCESSING_MODE'] === 'Y')
            {
                $arElement = $element->GetFields();
                $arElement['PROPERTIES'] = $element->GetProperties();
            }
            else
            {
                $arElement = $element;
            }

            $this->arResult['ELEMENTS'][] = $arElement;

            $this->setResultCacheKeys(array('NAV_CACHED_DATA'));
        }

        if ($this->arParams['SET_404'] === 'Y' && empty($this->arResult['ELEMENTS']))
        {
            $this->return404();
        }

        $this->generateNav($rsElements);
    }
}