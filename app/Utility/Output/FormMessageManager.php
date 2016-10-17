<?php
namespace App\Utility\Output;

use App\Model\User as User;
use Bridge\Session as Session;

/**
 *  管理 form 的訊息串
 *  除了管理 form field messages 之外
 *  也包含 存儲/清除 儲存訊息的一組 session 值
 *
 *  Example:
 *
 *      $messages = array();
 *      $messages['name']  = 'name 不正確';
 *      $messages['topic'] = '該欄位必填';
 *      FormMessageManager::setFieldMessages( $messages );
 *
 *      $message = array('password' => 'password 不正確');
 *      FormMessageManager::addFieldMessage( $message );
 *
 *      if ( FormMessageManager::hasError() ) {
 *          FormMessageManager::addErrorResultMessage('Error');
 *      }
 *      else {
 *          FormMessageManager::addSuccessResultMessage('Success');
 *      }
 *
 *
 */
class FormMessageManager
{

    /**
     *
     */
    protected static $_bridge = 'Bootstrap4';

    /**
     *  儲存所有的訊息
     */
    protected static $_messages = array();

    /**
     *  重新設定多組訊息
     *  @param array
     */
    public static function setFieldMessages ($fieldMessages)
    {
        self::$_messages = $fieldMessages;
    }

    /**
     *  新增 一組訊息
     *  @param array
     */
    public static function addFieldMessage ($fieldMessage)
    {
        if (!is_array($fieldMessage)) {
            return;
        }
        foreach ( $fieldMessage as $field => $value ) {
            // 只取一組
        }
        self::$_messages[ $field ] = $value;
    }

    /**
     *  取得欄位訊息
     *  @return string or null
     */
    public static function getFieldMessage ($field)
    {
        if (isset(self::$_messages) &&
            isset(self::$_messages[$field])
        ) {
            return self::$_messages[$field];
        }
        return null;
    }

    /**
     *  FormMessageManager 只管理訊息
     *  要怎麼顯示 theme 請使用另外的 class 處理
     */
    public static function factoryTheme ($field)
    {
        $className = 'App\\Utility\\Output\\FormMessageManager\\' . self::$_bridge;
        $message = self::getFieldMessage($field);
        return new $className($field, $message);
    }

    /**
     *  該 form 是否有錯誤訊息
     *  只要有一個欄位有錯誤訊息, 就會傳回 true
     *  @return boolean
     */
    public static function hasError()
    {
        if( !is_array(self::$_messages) ) {
            return true;
        }
        if( 0 !== count(self::$_messages) ) {
            return true;
        }
        return false;
    }




    /* ================================================================================
        將臨時訊息儲存於 session 之中, 顯示完之後需要刪除該訊息, 以避免重覆顯示
    ================================================================================ */

    /**
     *  取得 form 操作之後的臨時訊息
     *  該訊息儲存於 session 之中
     *  @return array
     */
    public static function getResultMessages()
    {
        $value = Session::get('result_message');
        if( !$value ) {
            return array();
        }
        return $value;
    }

    /**
     *  append success message
     *  @param string message
     */
    public static function addSuccessResultMessage( $string='' )
    {
        self::_addResultMessage($string, 'success');
    }

    /**
     *  append error message
     *  @param string message
     */
    public static function addErrorResultMessage( $string='' )
    {
        self::_addResultMessage($string, 'error');
    }

    /**
     *  清除臨時訊息, 通常顯示完之後就會馬上清除
     *  該訊息儲存於 session 之中
     */
    public static function clearResultMessages()
    {
        Session::remove('result_message');
    }

    // --------------------------------------------------------------------------------
    // 
    // --------------------------------------------------------------------------------

    /**
     *  append form 操作之後的臨時訊息
     */
    private static function _addResultMessage( $string, $type )
    {
        $type = strtolower($type);

        $values = Session::get('result_message');
        if( !$values ) {
            $values = array();
            $values[] = array('message'=>$string,'type'=>$type);
        }
        else {
            $values[] = array('message'=>$string,'type'=>$type);
        }

        Session::set('result_message', $values );
    }


}