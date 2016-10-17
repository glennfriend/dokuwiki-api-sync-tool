<?php

class DaoHelper
{
    /**
     *  底線式的變數名稱 轉化為 駝峰式
     *  example:
     *      user_id     => userId
     *      hello_a_b_c => HelloABC
     *
     */
    public static function convertUnderlineToVarName($name)
    {
        $result = '';
        foreach (explode('_', $name) as $tag) {
            if (!$result) {
                $result = $tag;
            }
            else {
                $result .= strtoupper($tag[0]) . substr($tag, 1);
            }
        }
        return $result;
    }
}
