<?php
namespace App\Shell;

/**
 *
 */
class BaseController
{
    /**
     *
     */
    public function __construct()
    {
        $this->baseDiLoader();
    }

    /**
     *
     */
    public function __call($method, $controllerArgs)
    {
        if (!method_exists($this, $method)) {
            throw new \Exception("API method '{$method}' is not exist!");
            exit;
        }

        // 如果有回傳值, 則不往下執行
        $result = $this->initBefore();
        if (null !== $result) {
            return $result;
        }

        // 如果有回傳值, 則不往下執行
        $result = $this->init();
        if (null !== $result) {
            return $result;
        }

        //
        return $this->$method();
    }


    /**
     *  僅供 extend controller rewrite
     *  最終端 Controller 請使用 init()
     */
    protected function initBefore()
    {
        // 僅供 extend controller rewrite
    }

    /**
     *  供給最終端 Controller 使用, 用來處理該 Controller 共用的流程
     */
    protected function init()
    {
        // 僅供 最終端 Controller rewirt
    }

    /**
     *  di loader
     *  @see https://github.com/symfony/dependency-injection
     *  @see http://symfony.com/doc/current/components/dependency_injection/factories.html
     *  @see http://symfony.com/doc/current/components/dependency_injection/introduction.html
     */
    private function baseDiLoader()
    {
        /*
        $basePath = conf('app.path');
        $di = di();
        $di->setParameter('app.path', $basePath);
        */

        /*
            Example:
                $di
                    ->register('example', 'Lib\Abc')
                    ->addArgument('%app.path%');                    // __construct
                    ->setProperty('setDb', [new Reference('db')]);  // ??
        */

        // queue
        // $di->register('queue', 'Bridge\Queue');

    }

}
