<?php

use App\Utility\Identity\UserManager;
// use Zend\Db\Sql\Select;
// use Zend\Db\Sql\Insert;
// use Zend\Db\Sql\Update;
// use Zend\Db\Sql\Delete;

class ZendModel3
{
    /**
     *  任何錯誤的情況, 要將狀態儲存在此
     *  object or null
     */
    protected $error = null;

    /**
     *  table name
     */
    protected $tableName = null;

    /**
     *  get method
     */
    protected $getMethod = null;

    /**
     *  table master field key
     */
    protected $pk = 'id';

    /**
     *
     */
    protected function getCache()
    {
        return di('cache');
    }

    /**
     *
     */
    protected function getLog()
    {
        return di('log');
    }

    /**
     * you can rewrite it
     * @return string|null
     */
    public function getFullCacheKey($value, $field)
    {
        if (!$field) {
            return null;
        }
        return "CACHE_MODELS.{$this->tableName}.". trim($field) .".". trim(strval($value));
    }

    /**
     *  get model error message
     *      - 如果有 自定義錯誤訊息, 就回傳該訊息
     *      - 如果有 exception 訊息, 就回傳該訊息
     *      - 如果有 update fail 的 update_message 訊息, 就回傳該訊息
     *      - 沒有就傳回預設內置的錯誤訊息
     *
     *  @return error-message|null
     */
    public function getModelError()
    {
        if (!$this->error) {
            return null;
        }
        if (isset($this->error['message'])) {
            return $this->error['message'];
        }
        if (isset($this->error['exception'])) {
            return $this->error['exception']->getMessage();
        }
        if (isset($this->error['update_message'])) {
            return $this->error['update_message'];
        }
        return 'Unknown model error';
    }

    /**
     *  set model error message
     */
    protected function setModelErrorMessage($message)
    {
        if (!$this->error) {
            $this->error = [];
        }
        $this->error['message'] = $message;
    }

    // --------------------------------------------------------------------------------
    //
    // --------------------------------------------------------------------------------

    /**
     *  Zend db query, access
     *
     *  @param Zend\Db\Sql\Select
     *  @return statement result object
     */
    public function query($select)
    {
        return $this->_query($select);
    }

    /**
     *  query by slave server
     */
    public function querySlave($select)
    {
        return $this->_query($select, false);
    }

    /**
     *  query by Adapter Type "master" or "slave"
     */
    protected function _query($select, $isMasterServer=true)
    {
        if ($isMasterServer) {
            $adapter    = BaseModel::getAdapter();
            $serverType = BaseModel::SERVER_TYPE_MASTER;
        }
        else {
            $adapter    = BaseModel::getSlaveAdapter();
            $serverType = BaseModel::SERVER_TYPE_SLAVE;
        }

        $zendSql = new Zend\Db\Sql\Sql($adapter);

        if (UserManager::isDebugMode() || 'training' === conf('app.env'))
        {
            // log
            self::getLog()->sql(
                $select->getSqlString( $adapter->getPlatform() ),
                $serverType
            );

            // developer tool
            // MonitorManager::sqlQuery( $select->getSqlString( $adapter->getPlatform() ) );
        }

        $this->error = null;
        try {
            $statement = $zendSql->prepareStatementForSqlObject($select);
            $results = $statement->execute();
        }
        catch (Exception $e) {
            $this->error = [
                'exception' => $e
            ];
            return false;
        }
        return $results;
    }

    /**
     *  Zend db execute, write
     *
     *  @param zend sql object
     *      Zend\Db\Sql\Insert
     *      Zend\Db\Sql\Update
     *      Zend\Db\Sql\Delete
     *  @return statement result object
     */
    public function execute($write)
    {
        $adapter = BaseModel::getAdapter();
        $sql = $write->getSqlString( $adapter->getPlatform() );

        if (UserManager::isDebugMode() || 'training' === conf('app.env'))
        {
            // log
            self::getLog()->sql($sql, BaseModel::SERVER_TYPE_MASTER);

            // developer tool
            // MonitorManager::executeQuery( $write->getSqlString( $adapter->getPlatform() ) );
        }

        $this->error = null;
        try {
            $statement = $adapter->query($sql);
            $result = $statement->execute();
        }
        catch( Exception $e ) {
            // insert/update/delete error
            // 例如: 重覆的鍵值 引發了 衝突
            $this->error = [
                'exception' => $e
            ];
            return false;
        }
        return $result;
    }

    /* ================================================================================
        write database
    ================================================================================ */

    /**
     * add object to database
     * @param object  - dbobject
     * @param boolean - false is default boolean, true is return insert id
     * @return  boolean or int
     */
    protected function addObject($object, $isReturnInsertId=false)
    {
        $row = $this->objectToArray( $object );

        $insert = new Zend\Db\Sql\Insert($this->tableName);
        $insert->values($row);
        $result = $this->execute($insert);
        if( !$result ) {
            return false;
        }

        if( $isReturnInsertId ) {
            return (int) $result->getGeneratedValue();
        }
        return true;
    }

    /**
     *  update object to database
     *  更新時, 若資料完全相同, 不會有更新的動作, 所以傳回值會是 0
     *
     * @param object
     * @return int, affected row count
     */
    protected function updateObject($object)
    {
        $row = $this->objectToArray($object);
        $pk = $this->pk;
        $pkValue = $row[$pk];
        unset($row[$pk]);

        $update = new Zend\Db\Sql\Update($this->tableName);
        $update->where(array( $pk => $pkValue ));
        $update->set($row);

        $result = $this->execute($update);
        if (!$result) {
            if (!$this->error) {
                $this->error = [];
            }
            $this->error['update_message'] = 'Update fail';
            return false;
        }
        return $result->count();
    }

    /**
     * delete object to database
     * @param key
     * @return int, affected row count
     */
    protected function deleteObject($key)
    {
        $delete = new Zend\Db\Sql\Delete($this->tableName);
        $delete->where(array( $this->pk => $key));

        $result = $this->execute($delete);
        if( !$result ) {
            return false;
        }
        return $result->count();
    }

    /**
     *  資料從 object 寫入到 database 之前要做資料轉換的動作
     */
    protected function objectToArray($object)
    {
        $data = array();
        foreach ($object->getTableDefinition() as $field => $item) {
            $type       = $item['type'];
            $varName    = DaoHelper::convertUnderlineToVarName($field);
            $method     = 'get' . strtoupper($varName[0]) . substr($varName, 1);
            $value      = $object->$method();

            if (is_object($value) || is_array($value)) {
                $value = serialize($value);
            }

            switch ($type) {
                case 'datetime':
                case 'timestamp':
                    $value = date('Y-m-d H:i:s', (int) $value);
                    break;
                case 'date':
                    $value = date('Y-m-d', (int) $value);
                    break;
            }

            $data[$field] = $value;
        }
        return $data;
    }

    /* ================================================================================
        access database
    ================================================================================ */

    /**
     *  get ZF2 Zend Db Select
     *  @return Zend\Db\Sql\Select
     */
    protected function getDbSelect($isSetDefaultValue=true)
    {
        $select = new Zend\Db\Sql\Select();
        if ( $isSetDefaultValue ) {
            $select->columns(array($this->pk));
            $select->from( $this->tableName );
        }
        return $select;
    }

    /**
     * get object and cache
     * @param string - field name
     * @param string - field value
     * @param boolean - cache or not
     * @return object or false
     */
    protected function getObject($field, $value, $isCache=true)
    {
        $fullCacheKey = self::getFullCacheKey($value, $field);
        if ($isCache) {
            $object = self::getCache()->get($fullCacheKey);
            if ($object) {
                return $object;
            }
        }
        else {
            self::getCache()->remove($fullCacheKey);
        }

        $select = $this->getDbSelect();
        $select->columns(array('*'));
        $select->where(array( $field => $value ));

        $result = $this->query($select);
        if( !$result ) {
            return false;
        }

        $row = $result->current();
        if( !$row ) {
            return false;
        }

        $object = $this->mapRow($row);
        if ($isCache) {
            self::getCache()->set($fullCacheKey, $object);
        }
        return $object;
    }

    /**
     *  find objects
     *  這裡可以選擇 adapter 使用 "master" or "slave"
     *
     *  @param $select   - Zend\Db\Sql\Select
     *  @param $opt      - option array
     *  @return objects or empty array
     */
    protected function findObjects(Zend\Db\Sql\Select $select, $opt=[])
    {
        $orderBy      = isset($opt['orderString']) ?       $opt['orderString'] : '' ;
        $page         = isset($opt['page'])        ? (int) $opt['page']        : 1  ;
        $itemsPerPage = isset($opt['perPage'])     ? (int) $opt['perPage']     : conf('db.per_page');

        $isMasterServer = true;
        if (isset($opt['serverType']) && BaseModel::SERVER_TYPE_SLAVE=== $opt['serverType']) {
            $isMasterServer = false;
        }

        if ($orderBy) {
            $select->order( trim($orderBy) );
        }
        if(-1 !== $page) {
            $page = (int) $page;
            if( $page == 0 ) {
                $page = 1;
            }
            $select->limit( $itemsPerPage );
            $select->offset( ($page-1)*$itemsPerPage );
        }
        $result = $this->_query($select, $isMasterServer);
        if ( !$result ) {
            return array();
        }

        $objects = array();
        $getMethod = $this->getMethod;
        while ($row = $result->next()) {
            $objects[] = $this->$getMethod($row[$this->pk]);
        };
        return $objects;
    }

    /**
     *  get row count
     *
     *  @param $condition - sql condition
     *  @param $opt       - option array
     *  @return int
     */
    protected function numFindObjects(Zend\Db\Sql\Select $select, $opt=[])
    {
        $isMasterServer = true;
        if (isset($opt['serverType']) && ZendModel::SERVER_TYPE_SLAVE === $opt['serverType']) {
            $isMasterServer = false;
        }

        $param = 'count(*)';
        $expression = array('total' => new \Zend\Db\Sql\Expression($param));
        $select->columns( $expression );

        $result = $this->_query($select, $isMasterServer);
        if( !$result ) {
            return 0;
        }

        $row = $result->current();
        return $row['total'];
    }

}
