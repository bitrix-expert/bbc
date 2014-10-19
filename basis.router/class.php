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

include_once dirname(__DIR__).'/basis/common.php';


/**
 * Basis complex component
 */
abstract class BasisRouter extends \CBitrixComponent
{
    use Common;

    /**
     * @var array Paths of templates default
     */
    protected $defaultUrlTemplates404;

    /**
     * @var array Variables template paths
     */
    protected $componentVariables;

    /**
     * @var string Template page
     */
    protected $page;

    /**
     * @var string Template page default
     */
    protected $defaultPage = 'list';

    /**
     * @var string Template page default for SEF mode
     */
    protected $defaultSefPage = 'list';

    /**
     * Set default parameters for SEF URL's
     */
    protected function setSefDefaultParams()
    {
        $this->defaultUrlTemplates404 = array(
            'list' => 'index.php',
            'detail' => '#ELEMENT_ID#/'
        );

        $this->componentVariables = array('ELEMENT_ID');
    }

    /**
     * Set type of the page
     */
    protected function setPage()
    {
        $urlTemplates = array();

        if ($this->arParams['SEF_MODE'] === 'Y')
        {
            $variables = array();

            $urlTemplates = \CComponentEngine::MakeComponentUrlTemplates(
                $this->defaultUrlTemplates404,
                $this->arParams['SEF_URL_TEMPLATES']
            );

            $variableAliases = \CComponentEngine::MakeComponentVariableAliases(
                $this->defaultUrlTemplates404,
                $this->arParams['VARIABLE_ALIASES']
            );

            $this->page = \CComponentEngine::ParseComponentPath(
                $this->arParams['SEF_FOLDER'],
                $urlTemplates,
                $variables
            );

            if (!$this->page)
            {
                $this->page = $this->defaultSefPage;
            }

            \CComponentEngine::InitComponentVariables(
                $this->page,
                $this->componentVariables,
                $variableAliases,
                $variables
            );
        }
        else
        {
            $this->page = $this->defaultPage;
        }

        $this->arResult['FOLDER'] = $this->arParams['SEF_FOLDER'];
        $this->arResult['URL_TEMPLATES'] = $urlTemplates;
        $this->arResult['VARIABLES'] = $variables;
        $this->arResult['ALIASES'] = $variableAliases;
    }

    final public function executeComponent()
    {
        try {
            $this->includeModules();
            $this->checkParams();

            $this->startAjax();
            $this->executeProlog();

            $this->setSefDefaultParams();
            $this->setPage();
            $this->getResult();
            $this->returnDatas($this->page);

            $this->executeEpilog();
            $this->stopAjax();
            $this->executeFinal();
        }
        catch (\Exception $e)
        {
            $this->catchException($e);
        }
    }
}