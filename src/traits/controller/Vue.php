<?php


namespace tpScriptVueCurd\traits\controller;


use think\App;
use think\exception\HttpResponseException;
use think\Request;
use tpScriptVueCurd\field\FilesField;
use tpScriptVueCurd\middleware\FieldMiddleware;
use tpScriptVueCurd\option\ConfirmException;
use tpScriptVueCurd\tool\ErrorCode;
use tpScriptVueCurd\traits\Func;

/**
 * Trait Vue
 * @property Request $request
 * @property APP $app
 * @package tpScriptVueCurd\traits\controller
 * @author tj 1079798840@qq.com
 */
trait Vue
{
    use Func;
    private static $vue_data = [];

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
        isset($this->middleware)||$this->middleware=[];
        $this->middleware[]=FieldMiddleware::class;
        parent::initialize();
        if(empty($this->app)){
            $this->app=app();
        }
        if(empty($this->request)){
            $this->request=$this->app->request;
        }
        if(empty($this->guid)){
            $this->guid=create_guid();
        }


        if ($this->checkIsVueAction()) {
            $this->app->view->config(['view_suffix' => 'vue']);
            $this->app->view->engine()->layout(static::getTplPath().'layout'.DIRECTORY_SEPARATOR.'default.vue');
        }
        if(empty(self::$vue_data['vueCurdAction'])){
            $this->assign('vueCurdAction',$this->request->action());
        }
        $this->assign('vueCurdVersion',static::vueCurdVersion());
        $this->assign('vueCurdController',$this->request->controller());
        $this->assign('vueCurdModule',$this->app->http->getName());
        $this->assign('guid',$this->guid);
        $this->assign('loginUrl',tpScriptVueCurdGetLoginUrl());
        $this->assign('vueCurdDebug',static::debug());
        $this->assign('themCssPath',tsvcThemCssPath());
    }


    /**
     * 模板变量|JS变量
     * @param $name
     * @param null $value
     */
    public function assign($name, $value = null)
    {
        if ($this->checkIsVueAction()) {
            if (is_array($name) && is_null($value)) {
                self::$vue_data = array_merge(self::$vue_data, $name);
            } else {
                self::$vue_data[$name] = $value;
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
            self::$vue_data = array_merge(self::$vue_data, $vars);
            $this->app->view->assign('vue_data_json', json_encode(self::$vue_data));
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
            self::$vue_data = array_merge(self::$vue_data, $vars);
            $this->app->view->assign('vue_data_json', json_encode(self::$vue_data));
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
        FilesField::setShowFileInfos();
        if(is_string($msgOrData)){
            $this->parentSuccess($msgOrData,$data,$url,$wait,$header);
        }
        throw new HttpResponseException(json([
            'code'=>1,
            'msg'=>is_string($data)?$data:'',
            'data'=>$msgOrData
        ]));
    }


    /**
     * 失败且设置 errorCode
     * @param string|Exception $msg
     * @param int $errorCode
     * @param bool|array $confirm 是否是返回给前台确认框
     * @throws \think\Exception
     */
    public function errorAndCode($msg,int $errorCode=0,$confirm=false):void{
        if(($msg instanceof \Exception)||is_subclass_of($msg,\Exception::class)){
            errorShowThrow($msg);
            $msg=$msg->getMessage();
        }

        if($confirm){
            if(empty($errorCode)){
                throw new \think\Exception('confirm为true时，errorCode不可为0，且是要唯一');
            }
            if(((int)$this->request->header('confirm-error-code'))===$errorCode){
                //用户已确认，不再返回前台信息，继续执行下面代码
                return;
            }
        }
        $this->error($msg,'',null,3,[],$errorCode,$confirm);
    }

    /**
     * 失败
     * @param string $msg
     * @param string $data
     * @param null $url
     * @param int $wait
     * @param array $header
     * @param int $errorCode
     * @param bool|array $confirm  需要使用 errorAndCode 来调用
     */
    public function error($msg = '', $data = '', $url = null, $wait = 3, array $header = [],int $errorCode=0,$confirm=false)
    {
        if($msg instanceof \think\exception\HttpResponseException||$msg instanceof \think\exception\HttpException
            ||is_subclass_of($msg,\think\exception\HttpResponseException::class)
            ||is_subclass_of($msg,\think\exception\HttpException::class)){
            throw $msg;
        }

        if(($msg instanceof \Exception)||is_subclass_of($msg,\Exception::class)){
            errorShowThrow($msg);
            $this->errorAndCode($msg->getMessage(), $msg->getCode(),$msg instanceof ConfirmException?[
                'okText'=>$msg->okText,
                'cancelText'=>$msg->cancelText,
                'title'=>$msg->title,
            ]:false);
            return;
        }

        if($data===''){
            $data=[];
        }

        if($confirm){
            $confirm=[
                'show'=>true,
                'okText'=>is_array($confirm)&&isset($confirm['okText'])?$confirm['okText']:'确认执行',
                'cancelText'=>is_array($confirm)&&isset($confirm['cancelText'])?$confirm['cancelText']:'取消',
                'title'=>is_array($confirm)&&isset($confirm['title'])?$confirm['title']:'操作确认',
            ];
        }else{
            $confirm=[
                'show'=>false,
//                'okText'=>'确认执行','cancelText'=>'取消','title'=>'操作确认',
            ];
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
            'confirm'=>$confirm,
            'errorCode'=>$errorCode,
        ];
        if ($type === 'html') {
            $tpl=app('config')->get('app.dispatch_error_tmpl');
            if($tpl){
                $this->app->view->engine()->layout(false);
                $response = view($tpl, $result);
            }else{
                $this->app->view->engine()->layout(static::getTplPath().'layout'.DIRECTORY_SEPARATOR.'jump.vue');
                $response = view(static::getTplPath().'jump/error.vue', ['vue_data_json'=>json_encode($result)]);
            }
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
            $tpl=app('config')->get('app.dispatch_success_tmpl');
            if($tpl){
                $this->app->view->engine()->layout(false);
                $response = view($tpl, $result);
            }else{
                $this->app->view->engine()->layout(static::getTplPath().'layout'.DIRECTORY_SEPARATOR.'jump.vue');
                $response = view(static::getTplPath().'jump/success.vue', ['vue_data_json'=>json_encode($result)]);
            }
        } elseif ($type === 'json') {
            $response = json($result);
        }
        throw new HttpResponseException($response);
    }


    final public static function getTplPath():string{
        return getVCurdDir().'tpl'.DIRECTORY_SEPARATOR;
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

    /**
     * 是否js与css调试，子类重写
     * @return bool
     */
    public static function debug():bool{
        return appIsDebug();
    }
}