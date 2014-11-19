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
 * Show page with element of the info-block
 */
class ElementsDetail extends Basis
{
    use Elements;

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
            $this->return404();
        }
    }

    protected function getResult()
    {
        $rsElement = \CIBlockElement::GetList(
            array(),
            $this->getParamsFilters(),
            false,
            array(
                'nTopCount' => 1
            ),
            $this->getParamsSelected()
        );

        if ($this->arParams['RESULT_PROCESSING_MODE'] === 'Y')
        {
            $processingMethod = 'GetNextElement';
        }
        else
        {
            $processingMethod = 'GetNext';
        }

        if ($element = $rsElement->$processingMethod())
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

            $this->arResult = array_merge($this->arResult, $arElement);

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