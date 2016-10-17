<?php
namespace App\Utility\Output\FormMessageManager;

/**
 *  Twitter bootstrap 4 theme
 */
class Bootstrap4
{
    /**
     *  @param string $field 欄位名稱
     *  @param string $message 錯誤訊息
     */
    public function __construct($field, $message)
    {
        $this->field    = $field;
        $this->message  = $message;

        // 關於 .sr-only  
        //      - 在沒有錯誤訊息的情況下顯示 .sr-only 
        //      - 該 class 表示要穩藏
        //      - 沒錯誤訊息就不需要顯示某些 html

        $this->group     = '';
        $this->input     = '';
        $this->hasError  = false;
        $this->hidden    = 'sr-only';

        if ($this->message) {
            $this->group    = 'has-warning';
            $this->input    = 'form-control-warning';
            $this->hasError = true;
            $this->hidden   = '';
        }
    }

}