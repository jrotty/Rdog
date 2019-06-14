<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 修改注册时默认用户组，贡献者可直接发布文章无需审核,前台注册支持用户输入密码
 * 
 * @package 权限狗
 * @author 泽泽
 * @version 1.1.6
 * @link http://qqdie.com
 */
class Rdog_Plugin extends Widget_Abstract_Users implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
      Typecho_Plugin::factory('Widget_Register')->register = array('Rdog_Plugin', 'zhuce'); 
	  Typecho_Plugin::factory('Widget_Register')->finishRegister = array('Rdog_Plugin', 'zhucewan');
	  Typecho_Plugin::factory('Widget_Contents_Post_Edit')->write = array('Rdog_Plugin', 'fabu');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {

    $yonghuzu = new Typecho_Widget_Helper_Form_Element_Radio('yonghuzu',array(
      'subscriber' => _t('关注者'),
      'contributor' => _t('贡献者'),
      'editor' => _t('编辑'),
      'administrator' => _t('管理员')
    ),'subscriber',_t('注册用户默认用户组设置'),_t('<p class="description">
不同的用户组拥有不同的权限，具体的权限分配表请<a href="http://docs.typecho.org/develop/acl" target="_blank" rel="noopener noreferrer">参考这里</a>.</p>'));
    $form->addInput($yonghuzu); 

           
    $tuozhan = new Typecho_Widget_Helper_Form_Element_Checkbox('tuozhan', 
    array('contributor-nb' => _t('勾选该选项让【贡献者】直接发布文章无需审核'),
),
    array(), _t('拓展设置'), _t(''));
    $form->addInput($tuozhan->multiMode());
     
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 插件实现方法
     * 
     * @access public
     * @return void
     */
public static function zhuce($v) {
  /*获取插件设置*/
   $yonghuzu = Typecho_Widget::widget('Widget_Options')->plugin('Rdog')->yonghuzu;
  $hasher = new PasswordHash(8, true);
  /*判断注册表单是否有密码*/
  if(isset(Typecho_Widget::widget('Widget_Register')->request->password)){
    /*将密码设定为用户输入的密码*/
    $generatedPassword = Typecho_Widget::widget('Widget_Register')->request->password;
  }else{
    /*用户没输入密码，随机密码*/
    $generatedPassword = Typecho_Common::randString(7);
  }
  /*将密码设置为常量，方便下个函数adu()直接获取*/
  define('passd', $generatedPassword);
  /*将密码加密*/
  $wPassword = $hasher->HashPassword($generatedPassword);
  /*设置用户密码*/
  $v['password']=$wPassword;
  /*将注册用户默认用户组改为插件设置的用户组*/
  $v['group']=$yonghuzu;
  /*返回注册参数*/
  return $v;
}
public static function zhucewan($obj) {
 /*获取密码*/
 $wPassword=passd;
 /*登录账号*/
 $obj->user->login($obj->request->name,$wPassword);
 /*删除cookie*/
 Typecho_Cookie::delete('__typecho_first_run');
 Typecho_Cookie::delete('__typecho_remember_name');
 Typecho_Cookie::delete('__typecho_remember_mail');
 /*发出提示*/
 $obj->widget('Widget_Notice')->set(_t('用户 <strong>%s</strong> 已经成功注册, 密码为 <strong>%s</strong>', $obj->screenName, $wPassword), 'success');
 /*跳转地址(后台)*/
 $obj->response->redirect($obj->options->adminUrl);
}
public static function fabu($con,$obj) {
  /*插件用户设置是否勾选*/    
  if (!empty(Typecho_Widget::widget('Widget_Options')->plugin('Rdog')->tuozhan) && in_array('contributor-nb',  Typecho_Widget::widget('Widget_Options')->plugin('Rdog')->tuozhan)){
  /*如果用户是贡献者临时给予编辑权限*/
  if($obj->author->group=='contributor'||$obj->user->group=='contributor'){
  $obj->user->group='editor';
  }}
  return $con;
}
 
}
