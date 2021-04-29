<?php


namespace tpScriptVueCurd\traits\controller;


use think\App;
use think\exception\HttpResponseException;
use think\Request;

/**
 * Trait Vue
 * @property Request $request
 * @property APP $app
 * @package tpScriptVueCurd\traits\controller
 * @author tj 1079798840@qq.com
 */
trait Vue
{
    private $vue_data = [];

    /***
     * 子类重写这个控制，判断当前action是否启用vue action
     * @return bool
     */
    protected function checkIsVueAction(){
        return true;
    }

    /**
     * tp初始化时会执行
     */
    public function initialize()
    {
        parent::initialize();
        if ($this->checkIsVueAction()) {
            $this->app->view->config(['view_suffix' => 'vue']);
            $this->app->view->engine()->layout(static::getTplPath().'layout'.DIRECTORY_SEPARATOR.'default.vue');
        }
        $this->assign('vueCurdVersion',static::vueCurdVersion());
        $this->assign('vueCurdAction',$this->request->action());
        $this->assign('vueCurdController',$this->request->controller());
        $this->assign('vueCurdModule',$this->app->http->getName());
        $this->assign('guid',$this->guid);
        $this->assign('loginUrl',tpScriptVueCurdGetLoginUrl());
    }


    /**
     * 模板变量|JS变量
     * @param $name
     * @param null $value
     */
    public function assign($name, $value = null)
    {
        if ($this->checkIsVueAction()) {
            if (is_array($name) && is_null(null)) {
                $this->vue_data = array_merge($this->vue_data, $name);
            } else {
                $this->vue_data[$name] = $value;
            }
        }

        $this->app->view->assign($name, $value);
    }


    /**
     * 解析模板
     * @param string $template
     * @param array $vars
     * @return mixed
     */
    public function fetch($template = '', $vars = [])
    {
        if ($this->checkIsVueAction()) {
            $this->vue_data = array_merge($this->vue_data, $vars);
            $this->app->view->assign('vue_data_json', json_encode($this->vue_data));
        }
        return $this->app->view->fetch($template, $vars);
    }


    /**
     * 解析内容
     * @param string $content
     * @param array $vars
     * @return mixed
     */
    public function display($content = '', $vars = [])
    {
        if ($this->checkIsVueAction()) {
            $this->vue_data = array_merge($this->vue_data, $vars);
            $this->app->view->assign('vue_data_json', json_encode($this->vue_data));
        }
        return  $this->app->view->display($content, $vars);
    }



    /**
     * 成功
     * @param string|array $msgOrData
     * @param string $data
     * @param null $url
     * @param int $wait
     * @param array $header
     * @return \think\response\Json
     */
    public function success($msgOrData = '', $data = '', $url = null, $wait = 3, array $header = [])
    {
        if(is_string($msgOrData)){
            $this->parentSuccess($msgOrData,$data,$url,$wait,$header);
        }
        return json([
            'code'=>1,
            'msg'=>is_string($data)?$data:'',
            'data'=>$msgOrData
        ]);
    }


    /**
     * 失败且设置 errorCode
     * @param string $msg
     * @param int $errorCode
     */
    public function errorAndCode(string $msg,int $errorCode=0):void{
        $this->error($msg,'',null,3,[],$errorCode);
    }

    /**
     * 失败
     * @param string $msg
     * @param string $data
     * @param null $url
     * @param int $wait
     * @param array $header
     * @param int $errorCode
     */
    public function error($msg = '', $data = '', $url = null, $wait = 3, array $header = [],int $errorCode=0)
    {
        if($data===''){
            $data=[];
        }


        if (is_null($url)) {
            $url = request()->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : app('route')->buildUrl($url)->__toString();
        }

        $type   =(request()->isJson() || request()->isAjax() || request()->isPost()) ? 'json' : 'html';
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
            'errorCode'=>$errorCode
        ];
        if ($type === 'html') {
            $response = view(app('config')->get('app.dispatch_error_tmpl'), $result);
        } elseif ($type === 'json') {
            $response = json($result);
        }
        throw new HttpResponseException($response);
    }



    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param mixed $data 返回的数据
     * @param string $url 跳转的 URL 地址
     * @param int $wait 跳转等待时间
     * @param array $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function parentSuccess($msg = '', $data = '', $url = null, $wait = 3, array $header = [])
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : app('route')->buildUrl($url)->__toString();
        }

        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = (request()->isJson() || request()->isAjax() || request()->isPost()) ? 'json' : 'html';
        if ($type === 'html') {
            $response = view(app('config')->get('app.dispatch_success_tmpl'), $result);
        } elseif ($type === 'json') {
            $response = json($result);
        }
        throw new HttpResponseException($response);
    }


    final public static function getTplPath():string{
        return root_path().'vendor'.DIRECTORY_SEPARATOR.'tj'.DIRECTORY_SEPARATOR.'tp-script-vue-curd'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR;
    }


    final public static function vueCurdVersion():string{
        $def='1.0.1';

        if(!is_file(root_path().'composer.lock')){
            return $def;
        }
        $content=file_get_contents(root_path().'composer.lock');
        $content = json_decode($content,true);
        if(!$content){
            return $def;
        }

        if(isset($content['packages'])){
            foreach ($content['packages'] as $v){
                if($v['name']==='tj/tp-script-vue-curd'){
                    return $v['version'];
                }
            }
        }

        if(isset($content['packages-dev'])){
            foreach ($content['packages-dev'] as $v){
                if($v['name']==='tj/tp-script-vue-curd'){
                    return $v['version'];
                }
            }
        }
        return $def;
    }
}