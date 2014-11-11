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
    use Pages;

    protected static $needModules = array('iblock');

    protected $checkParams = array(
        'IBLOCK_TYPE' => array('type' => 'string'),
        'IBLOCK_ID' => array('type' => 'int')
    );

    protected function getResult()
    {
        $rsElements = \CIBlockElement::GetList(
            array(),
            array_merge(
                array(
                    'IBLOCK_TYPE' => $this->arParams['IBLOCK_TYPE'],
                    'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                    'SECTION_ID' => $this->arParams['SECTION_ID'],
                    'ACTIVE' => 'Y'
                ),
                $this->arParams['EX_FILTER']
            ),
            false,
            $this->navParams,
            array_merge(
                array(
                    'ID',
                    'IBLOCK_ID',
                    'NAME'
                ),
                $this->getSelectedFields()
            )
        );

        while ($arElement = $rsElements->Fetch())
        {
            $this->arResult['ELEMENTS'][] = $arElement;
        }

        if ($this->arParams['SET_404'] === 'Y' && empty($this->arResult['ELEMENTS']) && empty($this->arParams['EX_FILTER']))
        {
            $this->return404();
        }

        $this->setNav($rsElements);
    }
}