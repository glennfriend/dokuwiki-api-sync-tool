<?php
namespace Bridge\Options;

class ViewEngine
{
    // --------------------------------------------------------------------------------
    // 
    // --------------------------------------------------------------------------------
    /**
     *  設定檔
     */
    private $_config = [];

    /**
     *  init
     */
    public function __construct(Array $config)
    {
        if (!isset($config['view_path'])) {
            throw new \Exception('ViewNormal error: view path not setting');
        }
        if (!file_exists($config['view_path'])) {
            throw new \Exception('ViewNormal error: view path not exist');
        }
        $this->_config['view_path'] = $config['view_path'];
    }

    /**
     *  取得 view templates path
     */
    public function getPath()
    {
        return $this->_config['view_path'];
    }

    // --------------------------------------------------------------------------------
    // 
    // --------------------------------------------------------------------------------
    /**
     *  自訂義的參數 儲存參數
     */
    private $_data;

    /**
     *  自訂義的參數 設定
     */
    public function set($key, $value)
    {
        $this->_data[$key] = $value;
    }

    /**
     *  自訂義的參數 取得
     */
    public function get($key, $defaultValue=null)
    {
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }
        return $defaultValue;
    }

    // --------------------------------------------------------------------------------
    // 
    // --------------------------------------------------------------------------------

    /**
     *  從樣板名稱 取得 完整的樣版路徑
     *      - example
     *          - _global.layout.public => /var/www/project/resource/views/_global.layout.public.phtml
     *          - home.auth.login       => /var/www/project/resource/views/home.auth.login.phtml
     *
     *  @param  string 樣版標示名稱
     *  @return string full path file, or empty string
     */
    public function getPathFile($dotName)
    {
        // validate to show error
        // a-z A-Z _ -
        $dots = explode('.', $dotName);
        foreach ($dots as $name) {
            if (!preg_match('/^[a-z_][a-zA-Z0-9_-]+$/', $name)) {
                // 不正確的樣板名稱
                return '';
                // throw new \Exception("Error: template name is wrong! ===> [{$dotName}]");
            }
        }

        // 併接
        $viewFile = $this->getPath();
        $count = count($dots);
        for ($i = 0; $i < $count; $i++) {
            if (($i+1)===$count) {
                $viewFile .= '.' . $dots[$i];
            }
            else {
                $viewFile .= '/' . $dots[$i];
            }
        }

        return $viewFile . '.phtml';
    }

    /**
     *  代入樣板的完整路徑, 取得內容
     *
     *  @param string template name
     *  @return text template content
     */
    public function renderTemplate($pathFile, $params)
    {
        if (false !== strstr($pathFile, '..')) {
            throw new \Exception('View path is error');
            exit;
        }

        $templateName = basename($pathFile);

        if (!preg_match('/^[a-zA-Z0-9\.\_]+$/', $templateName)) {
            throw new \Exception('View name ['. htmlspecialchars($templateName) .'] is error');
            exit;
        }

        $tmp = explode('\\', get_class($this));
        $className = strtolower($tmp[count($tmp)-1]);

        if (!file_exists($pathFile)) {
            throw new \Exception('View file "'. htmlspecialchars($pathFile) .'" not found!');
            exit;
        }

        // event
        if ($event = $this->get('renderBeforeEvent')) {
            $event();
        }

        // load template
        $___path    = $pathFile;
        $___params  = $params;
        $render = function() use ($___path, $___params) {
            // EXTR_SKIP - 如果有沖突，覆蓋已有的變量
            extract($___params, EXTR_OVERWRITE);
            include $___path;
        };

        ob_start();
            $render();
            $output = ob_get_contents();
        ob_end_clean();

        preg_match_all("/{{([a-zA-z0-9\.\-\_]+)}}/s", $output, $matchAll);
        if ($matchAll) {
            foreach ($matchAll[0] as $index => $match) {
                $templateTag  = $matchAll[0][$index];
                $templateName = $matchAll[1][$index];
                if ('content'===$templateName) {
                    continue;
                }

                $pathFile = $this->getPathFile($templateName);
                $templateContent = $this->renderTemplate($pathFile, $params);
                $output = str_replace($templateTag, $templateContent, $output);
            }
        }

        return $output;
    }

    /**
     *
     */
    public function render($pathFile, $params)
    {
        $output = $this->renderTemplate($pathFile, $params);

        // load layout
        $layoutTemplate = '';
        $layout = $this->get('layout');
        if (!$layout) {
            return $output;
        }

        //
        $layoutFilePath = $this->getPathFile($layout);
        $layoutContent = $this->renderTemplate($layoutFilePath, $params);

        // 最後換置 template 中的主體 {{content}}
        return str_replace("{{content}}", $output, $layoutContent);
    }

}
