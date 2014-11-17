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
            $this->getParamsFilter(),
            false,
            $this->getParamsNavStart(),
            $this->getParamsSelected()
        );

        while ($element = $rsElements->GetNext())
        {
            $this->arResult['ELEMENTS'][] = $element;
        }

        if ($this->arParams['SET_404'] === 'Y' && empty($this->arResult['ELEMENTS']) && empty($this->arParams['EX_FILTER']))
        {
            $this->return404();
        }

        $this->setNav($rsElements);
    }
}