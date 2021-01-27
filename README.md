<div align="center">
 <br>
<h1 align="center">tp-script-vue-curd</h1>
 <br>
thinkphp6 script方式引入vue 自动生成curd逻辑
 <br>
</div>
 <br>
<p align="center">
    <a href="#">
        <img src="https://img.shields.io/badge/Vue-3.0.0+-green.svg" alt="Vue Version">
    </a>
    <a href="#">
        <img src="https://img.shields.io/badge/ThinkPHP-6.0.0+-green.svg" alt="ThinkPHP Version">
    </a>
      <a href="#">
        <img src="https://img.shields.io/badge/ant design vue-2.0.0+-green.svg" alt="ant-design-vue Version">
    </a>
</p>

> 项目初衷

只需在 模型表 中配置字段信息，就可以生成 列表、筛选、编辑 页面，且页面灵活，可适应多种情况。

### 生成页面预览

1. 列表![列表](https://images.gitee.com/uploads/images/2021/0127/142357_7b7ac128_370098.png "1.png")
2. 编辑![编辑](https://images.gitee.com/uploads/images/2021/0127/142742_07cc8b32_370098.png "2.png")
3. 子表![子表](https://images.gitee.com/uploads/images/2021/0127/142758_e14af033_370098.png "3.png")

### 使用步奏


   1. 安装
        ```
        composer require tj/tp-script-vue-curd
        ```
   2. 复制composer安装后的文件‘vendor/tj/tp-script-vue-curd/copy/tp-script-vue-curd-static.php’到项目public目录下。<br><br>
   3. 复制composer安装后的文件‘vendor/tj/tp-script-vue-curd/copy/tp-script-vue-curd-config.php’到项目引用目录中，如：app/admin/。<br><br>
   4. 在应用目录中的common.php文件（如：app/admin/common.php）中,引入tp-script-vue-curd-config.php。
        ```
        require_once 'tp-script-vue-curd-config.php';
        ```
        （注意：此时common.php文件与tp-script-vue-curd-config.php在同一目录中）
        (注意：如果应用目录没有common.php文件，需在应用目录中创建common.php文件)<br><br>
   5. 修改复制出来的文件[ _tp-script-vue-curd-config.php_ ]，配置相关代码。<br><br>
   6. 基本配置已完成。 **现在是使用示例** ：

       - 1）数据库中建项目表
         
             CREATE TABLE `tp-script-vue-curd-testdb`.`project`  (
                     `id` int NOT NULL AUTO_INCREMENT,
                     `name` varchar(120) NOT NULL DEFAULT '' COMMENT '项目名称',
                     `type` varchar(30) NOT NULL DEFAULT '' COMMENT '项目分类',
                     `start_time` int NOT NULL DEFAULT 0 COMMENT '项目开始时间',
                     `end_time` int NOT NULL DEFAULT 0 COMMENT '项目结束时间',
                     `money` decimal(11, 2) NOT NULL DEFAULT 0 COMMENT '项目金额',
                     `create_time` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
                     `create_admin_id` int(11) NOT NULL DEFAULT 0 COMMENT '创建人',
                     `update_time` int(10) NOT NULL DEFAULT 0 COMMENT '更新时间',
                     `delete_time` int(10) NOT NULL DEFAULT 0 COMMENT '删除时间',
                     `delete_admin_id` int(11) NOT NULL DEFAULT 0 COMMENT '删除人',
                     `update_admin_id` int(11) NOT NULL DEFAULT 0 COMMENT '修改人',
                     PRIMARY KEY (`id`)
                   ) COMMENT = '项目表';
   
       - 2）建立模型文件
   
               a）在模型目录（无规定）中创建Project模型文件
               b）在控制器目录（无规定）中创建Project控制器文件
   
       - 3）配置字段等信息
       - 3.1）模型简单配置示例
         
              <?php
              namespace app\model;

              use tpScriptVueCurd\field\DateField;
              use tpScriptVueCurd\field\DecimalField;
              use tpScriptVueCurd\field\RadioField;
              use tpScriptVueCurd\field\StringField;
              use tpScriptVueCurd\FieldCollection;
              
              /**
               * 需要继承 \tpScriptVueCurd\base\model\BaseModel 模型类
               * Class Project
               * @package app\model
               */
              class Project extends \tpScriptVueCurd\base\model\BaseModel
              {
                  /**
                   * 关联的控制器配置
                   * @return string
                   */
                  public static function getControllerClass(): string
                  {
                      return \app\controller\Project::class;
                  }
              
                  /**
                   * 模型名称
                   * @return string
                   */
                  public static function getTitle(): string
                  {
                      return '项目信息';
                  }
              
                  /**
                   * 表字段配置
                   * @return FieldCollection
                   */
                  public function fields(): FieldCollection
                  {
                      //FieldCollection 继承了thinkphp6的 \think\Collection，所以返回的对象可以使用 \think\Collection的方法，比如->toArray();
                      return FieldCollection::make([
                          //input输入框
                          StringField::init('name','项目名称')
                              ->required(true) //必填
                              ->listShow(true)//在列表中显示此字段
                          //单选
                          ,RadioField::init('type','项目类型')
                              ->items(['A类项目','B类项目'])//选项
                              ->required(true) //必填
                              ->listShow(true)//在列表中显示此字段
                          //日期选择
                          ,DateField::init('start_time','项目开始时间')
                              ->required(true) //必填
                              ->listShow(true)//在列表中显示此字段
                          //日期选择
                          ,DateField::init('end_time','项目结束时间')
                              ->required(true) //必填
                              ->listShow(true)//在列表中显示此字段
                          //小数
                          ,DecimalField::init('salary', '项目金额')
                              ->listShow(true)
                              ->ext('元')//薪资单位，结尾
                              ->required(true)
                              //筛选配置
                              ->doFilter(fn(\tpScriptVueCurd\filter\BetweenFilter $filter)=>$filter->setItems([
                                  ['start'=>0,'end'=>999.99,'title'=>'1000元以下'],
                                  ['start'=>1000,'end'=>2999.99,'title'=>'1000到3000(不包含)'],
                                  ['start'=>3000,'end'=>4999.99,'title'=>'3000到5000（不包含）'],
                                  ['start'=>5000,'end'=>9999.99,'title'=>'5000到10000（不包含）'],
                                  ['start'=>10000,'end'=>0,'title'=>'10000元及以上'],
                              ])),
                      ]);
                  }
              }
         
      - 3.2）控制器简单配置示例
               
            <?php
              namespace app\controller;
              
              use app\BaseController;
              class Project extends BaseController
              {
                  //不需要 extends ，但是需要 use trait文件：\tpScriptVueCurd\base\controller\BaseController;
                  use \tpScriptVueCurd\base\controller\BaseController;
              
                  /**
                   * 配置控制器对应的模型类
                   * @return string
                   */
                  public static function modelClassPath(): string
                  {
                      return \app\model\Project::class;
                  }
              }
   
       - 4）然后浏览器访问这个控制器的index方法就能查看结果了（如：http://127.0.0.1:8000/index.php/project/index）。更多高级使用方法，后期再写一个文档
   
### 相关示例项目

gitee：[tp-script-vue-curd-test，点击访问](https://gitee.com/tjztjspz/tp-script-vue-curd-test)

