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
 * Work in progress…
 */
class ElementsList extends Basis
{
    protected static $needModules = array('iblock1');

    protected function getResult()
    {
        $rsElements = \CIBlockElement::GetList(
            array(),
            array(
                'IBLOCK_TYPE' => $this->arParams['IBLOCK_TYPE'],
                'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                'ACTIVE' => 'Y'
            ),
            false,
            array(
                'nTopCount' => $this->arParams['ELEMENTS_COUNT']
            ),
            array(
                'ID',
                'IBLOCK_ID',
                'NAME'
            )
        );

        while ($arElement = $rsElements->Fetch())
        {
            $this->arResult['ELEMENTS'][] = $arElement;
        }
    }
}