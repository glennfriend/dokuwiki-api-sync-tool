<?php
namespace App\Shell;

/**
 *
 */
class MainController extends BaseController
{
    /**
     *  initBefore() 僅供 extend controller rewrite
     *  最終端 Controller 請使用 init()
     */
    protected function initBefore()
    {
        include 'mainHelper.php';
    }

}
