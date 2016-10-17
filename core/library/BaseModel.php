<?php

/*
    Zend Db sample:
        https://gist.github.com/ralphschindler/3949548
        http://www.maltblue.com/tutorial/zend-db-sql-the-basics
        http://www.maltblue.com/tutorial/zend-db-sql-select-easy-where-clauses
        http://framework.zend.com/manual/2.0/en/modules/zend.db.sql.html

    update at 2016-10-06
*/
class BaseModel
{
    /**
     *
     */
    const ADAPTER_CLASS = Zend\Db\Adapter\Adapter::class;

    /**
     *
     */
    const SERVER_TYPE_MASTER = 'master';

    /**
     *
     */
    const SERVER_TYPE_SLAVE  = 'slave';

    // --------------------------------------------------------------------------------
    //  transaction
    // --------------------------------------------------------------------------------

    /**
     *
     */
    static public function begin()
    {
        $this->getAdapter()->getDriver()->getConnection()->beginTransaction();
    }

    /**
     *
     */
    static public function commit()
    {
        $this->getAdapter()->getDriver()->getConnection()->commit();
    }

    /**
     *
     */
    static public function rollback()
    {
        $this->getAdapter()->getDriver()->getConnection()->rollBack();
    }

    // --------------------------------------------------------------------------------
    //
    // --------------------------------------------------------------------------------

    /**
     *  get master adapter
     *  cache the $adapter
     *
     *  @return adapter
     */
    static public function getAdapter()
    {
        static $adapter;

        if ($adapter) {
            return $adapter;
        }
        $adapter = self::_getAdapterByParam(
            conf('db.mysql.host'),
            conf('db.mysql.db'),
            conf('db.mysql.user'),
            conf('db.mysql.pass')
        );
        return $adapter;
    }

    /**
     *  在 主從式資料庫 (master/slave) 之下, 取得的 slave adapter
     *  cache the $adapterSlave
     *
     *  @return adapter
     *
     *  注意!
     *      Slave 會有時間上的延遲
     *      所以 "不應該" 一切都以 "寫入用 master, 讀取用 slave" 的概念來決定使用方式
     *      只適合取得 "不準確" 的資料
     *
     *      簡單來說就是
     *          寫入 請用 master
     *          讀取 請用 master
     *          不重視資料即時正確性 請用 slave
     *
     */
    static public function getSlaveAdapter()
    {
        static $adapterSlave;

        if ($adapterSlave) {
            return $adapterSlave;
        }
        $adapterSlave = self::_getAdapterByParam(
            conf('db.mysql_slave.host'),
            conf('db.mysql_slave.db'),
            conf('db.mysql_slave.user'),
            conf('db.mysql_slave.pass')
        );
        return $adapterSlave;
    }

    /**
     *  build Zend Db Adapter
     */
    static protected function _getAdapterByParam($host, $db, $user, $pass)
    {
        $adapterClass = self::ADAPTER_CLASS;
        return new $adapterClass([
            'driver'    => 'Pdo_Mysql',
            'dsn'       => 'mysql:host='. $host .';dbname='. $db,
            'username'  => $user,
            'password'  => $pass,
            'driver_options' => [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
            ]
        ]);

    }

}
