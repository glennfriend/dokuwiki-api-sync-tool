<?php
/**
 *  該程式於 model 呼叫使用
 *  代入特定的 white-list array 
 *  將會處理裡面的參數
 *
 *  欄位格式
 *      [
 *          'id'        => 5,
 *          'orderId'   => 10,
 *      ]
 *
 *  白名單格式 $whiteList example
 *      [
 *          'id'            => 'order_item.id',
 *          'orderId'       => 'order_item.order_id',
 *          'status'        => 'order_item.status',
 *          'sku'           => 'order_item.sku',
 *          'orderId'       => 'order.order_number',
 *          'orderNumber'   => 'order.order_number',
 *      ]
 *
 *  options 格式
 *      [
 *          'order',
 *          'page',
 *          'perPage',
 *          'serverType',
 *      ]
 *
 */
class ZendModelWhiteListHelper
{
    /**
     *  處理特定封裝的資料格式
     *  處理資料後, 產出合乎規格的資料資料格式
     */
    public static function perform(& $fields, $mappingNameToField, & $options)
    {
        \ZendModelWhiteListHelper::validateFields($fields, $mappingNameToField);
      //\ZendModelWhiteListHelper::convertFieldValueNullToEmpty($fields);
        \ZendModelWhiteListHelper::validateOptions($options);
        \ZendModelWhiteListHelper::convertOptionOrder($mappingNameToField, $options);
    }

    /**
     *  如果代入的欄位不在白名單, 會 trigger error
     *  這是一個 developer 的工具程式
     *
     *  @param array $fields,    標準欄位格式
     *  @param array $allowList, 予許的欄位白名單
     */
    public static function validateFields(Array $fields, Array $allowList)
    {
        foreach ($fields as $name => $value) {
            if (!in_array($name, array_keys($allowList))) {
                $show = preg_replace("/[^a-zA-Z0-9_]+/", '', $name);
                trigger_error("Custom Model Error: field not found '{$show}'", E_USER_ERROR);
            }
        }
    }

    /**
     *  如果代入的選項不在白名單, 會 trigger error
     *  這是一個 developer 的工具程式
     *
     *  @param array $options, 標準欄位格式
     *  @param array $allowFields, 予許的欄位白名單
     */
    public static function validateOptions(Array $options)
    {
        $allowList = [
            'order',
            'page',
            'perPage',
            'serverType',
        ];
        foreach ($options as $name => $value) {
            if (!in_array($name, $allowList)) {
                $show = preg_replace("/[^a-zA-Z0-9_]+/", '', $name);
                trigger_error("Custom Model Error: option not found '{$show}'", E_USER_ERROR);
            }
        }
    }

    /**
     *  將陣列資料的 null 轉換為 空字串
     *  在我們的寫法規範, null 值視為 "不處理的 SQL"
     *      "name" => null
     *      --> 不處理
     *
     *  但是空值是 "要處理的 SQL"
     *      "name" => ""
     *      --> name = ""
     *
     *  by reference
     *
     *  @param array $options, 標準欄位格式
     */
    /*
    public static function convertFieldValueNullToEmpty(Array & $fields)
    {
        foreach ($fields as $field => $value) {
            if ( is_null($fields[$field]) ) {
                $fields[$field] = '';
            }
        }
    }
    */

    /**
     *  convert order-by array to string
     *  by reference
     *
     *  如果欄位名稱沒包含在白名單內
     *  會觸發例外 Exception
     *
     *  該程式 會 區分大寫小
     *
     *  order-by example:
     *
     *      ['order] => [
     *          'id'        => 'asc',
     *          'user_name' => 'desc',
     *      ]
     *
     *  convert to
     *
     *      ['orderString'] => 'id ASC, user_name DESC',
     *
     *  @return array
     */
    public static function convertOptionOrder(Array $whiteList, Array & $options)
    {
        // validate order key
        $orderKey = 'order';

        // 如果欄位不存在, 則不處理
        if ( !isset($options[$orderKey]) ) {
            return $options;
        }
        $orderItems = $options[$orderKey];

        // 必須是 array
        if ( !is_array($options[$orderKey]) ) {
            throw new Exception('Error, Model order-by need is array');
        }

        // validate name
        foreach ($orderItems as $name => $value) {
            if (!in_array($name, array_keys($whiteList))) {
                throw new Exception('Error, Model order-by non-allow field: ['. $name . ']');
            }
        }

        // render SQL order-by string
        $results = [];
        foreach ($orderItems as $name => $value) {
            switch (strtolower($value)) {
                case 'asc':
                case 'desc':
                    break;
                default:
                    throw new Exception('Error, Model order-by value need is ASC or DESC');
            }
            $results[] = $whiteList[$name] . ' ' . $value;
        }
        $orderString = join(',', $results);

        $options['orderString'] = $orderString;
    }


    /**
     *  包裏多數資料 (陣列格式) 的 Zend Db 語法
     *
     *  由於 Zend Db 在包裏 nest, unnset 之間的條件必須在一個式子裡面
     *  所以這裡用比較醜的方式來重新包裏 ( values[0] and values[1] and ... )
     *
     *  example:
     *
     *      $select
     *          ->where
     *          ->and
     *          ->nest
     *              ->like( $field, '%'. $values[0] .'%' )
     *              ->or
     *              ->like( $field, '%'. $values[1] .'%' )
     *              ->or
     *              ->like( $field, '%'. $values[2] .'%' )
     *          ->unnest
     *      ;
     *
     */
    public static function nestLikeOr(Zend\Db\Sql\Select $select, $field, Array $values)
    {
        if (1 == count($values)) {
            $select
                ->where
                ->and
                ->nest
                    ->like( $field, '%'. $values[0] .'%' )
                ->unnest
            ;
        }
        elseif (2 == count($values)) {
            $select
                ->where
                ->and
                ->nest
                    ->like( $field, '%'. $values[0] .'%' )
                    ->or
                    ->like( $field, '%'. $values[1] .'%' )
                ->unnest
            ;
        }
        elseif (3 == count($values)) {
            $select
                ->where
                ->and
                ->nest
                    ->like( $field, '%'. $values[0] .'%' )
                    ->or
                    ->like( $field, '%'. $values[1] .'%' )
                    ->or
                    ->like( $field, '%'. $values[2] .'%' )
                ->unnest
            ;
        }
        elseif (4 == count($values)) {
            $select
                ->where
                ->and
                ->nest
                    ->like( $field, '%'. $values[0] .'%' )
                    ->or
                    ->like( $field, '%'. $values[1] .'%' )
                    ->or
                    ->like( $field, '%'. $values[2] .'%' )
                    ->or
                    ->like( $field, '%'. $values[3] .'%' )
                ->unnest
            ;
        }
        elseif (5 == count($values)) {
            $select
                ->where
                ->and
                ->nest
                    ->like( $field, '%'. $values[0] .'%' )
                    ->or
                    ->like( $field, '%'. $values[1] .'%' )
                    ->or
                    ->like( $field, '%'. $values[2] .'%' )
                    ->or
                    ->like( $field, '%'. $values[3] .'%' )
                    ->or
                    ->like( $field, '%'. $values[4] .'%' )
                ->unnest
            ;
        }
        else {
            throw new Exception('f324093j4806384506k8320495k830495k83405934');
        }

    }

}
