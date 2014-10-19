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


/**
 * Basis component
 */
abstract class Basis extends \CBitrixComponent
{
    use Common;

    final public function executeComponent()
    {
        try {
            $this->includeModules();
            $this->checkParams();
            $this->startAjax();
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