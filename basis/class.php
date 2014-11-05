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

include_once __DIR__.'/common.php';
include_once __DIR__.'/pages.php';


/**
 * Abstraction basis component
 */
abstract class Basis extends \CBitrixComponent
{
    use Common;

    /**
     * Auto executing methods of prolog / epilog in the traits
     */
    const TRAITS_AUTO_EXECUTE = true;

    /**
     * @var array Used traits
     */
    private $usedTraits;

    /**
     * Executing methods prolog, getResult and epilog included traits
     *
     * @param string $type prolog, getResult or epilog
     */
    final private function executeTraits($type)
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

            case 'getResult':
                $type = 'GetResult';
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
        if (static::TRAITS_AUTO_EXECUTE)
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

    final public function executeComponent()
    {
        try {
            $this->getUsedTraits();
            $this->includeModules();
            $this->checkParams();
            $this->startAjax();
            $this->executeTraits('prolog');
            $this->executeProlog();

            if ($this->startCache())
            {
                $this->getResult();
                $this->executeTraits('getResult');

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
            $this->stopAjax();
            $this->executeFinal();
        }
        catch (\Exception $e)
        {
            $this->catchException($e);
        }
    }
}