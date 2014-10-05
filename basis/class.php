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

include_once __DIR__.'/trait.php';


/**
 * Basis component
 */
abstract class Basis extends \CBitrixComponent
{
    use BasisTrait;

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
                $this->returnDatas();
                $this->writeCache();
            }

            $this->executeEpilog();
            $this->stopAjax();
        }
        catch (\Exception $e)
        {
            $this->abortCache();
            $this->catchError($e);
        }
    }
}