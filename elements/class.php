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

namespace Expert\Bbc\Components;


if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

\CBitrixComponent::includeComponentClass(basename(dirname(__DIR__)).':basis.router');


class ElementsRouter extends BasisRouter
{
    protected $defaultSefPage = 'index';

    protected function setSefDefaultParams()
    {
        $this->defaultUrlTemplates404 = array(
            'index' => '',
            'section' => '#SECTION_ID#/',
            'detail' => '#SECTION_ID#/#ELEMENT_ID#/'
        );

        $this->componentVariables = array(
            'SECTION_ID',
            'SECTION_CODE',
            'ELEMENT_ID',
            'ELEMENT_CODE'
        );
    }
}