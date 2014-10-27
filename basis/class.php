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

    const TRAITS_AUTO_EXECUTE = true;

    private $usedTraits;

    final private function executeTraits($type)
    {
        if (!empty($this->usedTraits))
        {
            $type = ($type === 'prolog') ? 'Prolog' : 'Epilog';

            foreach ($this->usedTraits as $trait => $name)
            {
                $method = 'execute'.$type.$name;

                if (method_exists($trait, $method))
                {
                    $this->$method();
                }
            }
        }
    }

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