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

include_once __DIR__.'/componenthelpers.php';
include_once __DIR__.'/common.php';
include_once __DIR__.'/elements.php';


/**
 * Abstraction basis component
 */
abstract class Basis extends \CBitrixComponent
{
    use Common;

    /**
     * @var bool Auto executing methods of prolog / epilog in the traits
     */
    protected $traitsAutoExecute = true;

    /**
     * @var array Used traits
     */
    private $usedTraits;

    /**
     * Executing methods prolog, getResult and epilog included traits
     *
     * @param string $type prolog, getResult or epilog
     */
    private function executeTraits($type)
    {
        if (empty($this->usedTraits))
        {
            return;
        }

        switch ($type)
        {
            case 'prolog':
                $type = 'Prolog';
            break;

            case 'main':
                $type = 'Main';
            break;

            default:
                $type = 'Epilog';
            break;
        }

        foreach ($this->usedTraits as $trait => $name)
        {
            $method = 'execute'.$type.$name;

            if (method_exists($trait, $method))
            {
                $this->$method();
            }
        }
    }

    /**
     * Set to $this->usedTraits included traits
     */
    private function getUsedTraits()
    {
        if ($this->traitsAutoExecute)
        {
            $reflection = new \ReflectionClass(get_called_class());

            $parentClass = $reflection;

            while (1)
            {
                foreach ($parentClass->getTraitNames() as $trait)
                {
                    $this->usedTraits[$trait] = bx_basename($trait);
                }

                if ($parentClass->name === __CLASS__)
                {
                    break;
                }

                $parentClass = $parentClass->getParentClass();
            }
        }
    }

    final public function executeBasis()
    {
        $this->getUsedTraits();
        $this->includeModules();
        $this->checkAutomaticParams();
        $this->checkParams();
        $this->startAjax();
        $this->executeTraits('prolog');
        $this->executeProlog();

        if ($this->startCache())
        {
            $this->executeMain();
            $this->executeTraits('main');

            if ($this->cacheTemplate)
            {
                $this->returnDatas();
            }

            $this->writeCache();
        }

        if (!$this->cacheTemplate)
        {
            $this->returnDatas();
        }

        $this->executeTraits('epilog');
        $this->executeEpilog();
        $this->executeFinal();
        $this->stopAjax();
    }

    public function executeComponent()
    {
        try {
            $this->executeBasis();
        }
        catch (\Exception $e)
        {
            $this->catchException($e);
        }
    }
}