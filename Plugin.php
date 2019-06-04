<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 修改注册时默认用户组，贡献者可直接发布文章无需审核
 * 
 * @package 权限狗
 * @author 泽泽
 * @version 1.0.0
 * @link http://qqdie.com
 */
class Rdog_Plugin implements Typecho_Plugin_Interface
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
      Typecho_Plugin::factory('Widget_Register')->register = array('Rdog_Plugin', 'sc');
      Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishPublish = array('Rdog_Plugin', 'sx');
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
public static function sc($v) {
  /*获取插件设置*/
  $yonghuzu = Typecho_Widget::widget('Widget_Options')->plugin('Rdog')->yonghuzu;
  /*将注册用户默认用户组改为插件设置的用户组*/
  $v['group']=$yonghuzu;
  /*返回注册参数*/
  return $v;
}
public static function sx($con,$obj) {
  /*插件用户设置是否勾选*/    
  if (!empty(Typecho_Widget::widget('Widget_Options')->plugin('Rdog')->tuozhan) && in_array('contributor-nb',  Typecho_Widget::widget('Widget_Options')->plugin('Rdog')->tuozhan)){
  /*如果用户是贡献者并且文章状态为待审核*/
    if($obj->status == 'waiting' && $obj->author->group=='contributor'){
    /*连接数据库，将文章状态改为publish即公开状态*/
    $db = Typecho_Db::get();
    $update = $db->update('table.contents')->rows(array('status'=>'publish'))->where('cid = ?', $obj->cid);
    $db->query($update);
    /*传递最新文章状态*/
    $obj->status = 'publish';
    }
  }
}


}
