<?php
/**
 * <p>Developer Helper</p>
*/
class PluginWfDeveloperhelper{
  function __construct($buto = false) {
    if($buto){
    }
  }
  /**
  */
  private $settings = null;
  /**
   <p>Init method.</p>
   */
  private function init(){
    if(!wfUser::hasRole("webmaster")){
      exit('Role webmaster is required!');
    }
    wfArray::set($GLOBALS, 'sys/layout_path', '/plugin/wf/developerhelper/layout');
    wfPlugin::includeonce('wf/array');
    $this->settings = new PluginWfArray(wfArray::get($GLOBALS, 'sys/settings/plugin_modules/'.wfArray::get($GLOBALS, 'sys/class').'/settings'));
  }
  /**
   <p>Start page.</p>
   */
  public function page_desktop(){
    $this->init();
    $page = $this->getYml('page/desktop.yml');
    $page = wfDocument::insertAdminLayout($this->settings, 1, $page);
    wfDocument::mergeLayout($page->get());
  }
  public function page_json_to_yml(){
    $this->init();
    $page = $this->getYml('page/json_to_yml.yml');
    $page = wfDocument::insertAdminLayout($this->settings, 1, $page);
    if(wfRequest::get('json')){
      $page->setById('json', 'innerHTML', wfRequest::get('json'));
    }
    $page->setById('yml', 'innerHTML', wfHelp::getYmlDump( json_decode(wfRequest::get('json'), true) ));
    wfDocument::mergeLayout($page->get());
  }
  public function page_unique_rows(){
    $this->init();
    $page = $this->getYml('page/unique_rows.yml');
    $page = wfDocument::insertAdminLayout($this->settings, 1, $page);
    if(wfRequest::get('column')){
      $rows = preg_split("/\r\n/", wfRequest::get('column'));
      $unique = array();
      foreach ($rows as $key => $row) {
        if(!isset($unique[$row])){
          $unique[$row] = array('count' => 1, 'value' => $row);
        }else{
          $unique[$row]['count'] = $unique[$row]['count']+1;
        }
      }
      $str = '';
      foreach ($unique as $key => $value) {
        $str .= $value['count']."\t".$value['value']."\n";
      }
      $page->setById('column', 'innerHTML', wfRequest::get('column'));
      $page->setById('column_unique', 'innerHTML', $str);
      $page->setById('alert_result', 'innerHTML', sizeof($unique).' uniqe rows of '.  sizeof($rows).'.');
    }
    wfDocument::mergeLayout($page->get());
  }
  public function widget_json(){
    $script = wfDocument::createHtmlElement('script', "var app = {class: '".wfArray::get($GLOBALS, 'sys/class')."'};");
    wfDocument::renderElement(array($script));
  }
  public function page_db_query(){
    if(!wfUser::hasRole('webmaster')){
      echo 'Saknar rättighet!'; 
      return null;
    }
    $this->init();
    wfPlugin::includeonce('wf/mysql');
    $page = $this->getYml('page/db_query.yml');
    $page = wfDocument::insertAdminLayout($this->settings, 1, $page);
    if(wfRequest::get('sql')){
      $mysql = new PluginWfMysql();
      $mysql->open($this->settings->get('mysql'));
      $rows = preg_split("/\r\n/", wfRequest::get('sql'));
      $str = '';
      foreach ($rows as $key => $sql) {
        if($sql){
          $row_str = '';
          $sql = str_replace('[uid]', wfCrypt::getUid(), $sql);
//          $sql = str_replace('[pwd]', generatePassword(), $sql);
          $rs = $mysql->runSql($sql, null);
          if(wfRequest::get('skip_count')!='on'){
            $row_str .= $rs['num_rows']."\t";
          }
          if($rs['num_rows'] > 0){
            foreach ($rs['data'][0] as $key3 => $value3) {
              $row_str .= $value3."\t";
            }
          }
          $row_str = substr($row_str, 0, strlen($row_str)-1);
          $str .= $row_str."\n";
        }else{
          $str .= "\n";
        }
      }
      $page->setById('sql', 'innerHTML', wfRequest::get('sql'));
      $page->setById('result', 'innerHTML', $str);
    }
    wfDocument::mergeLayout($page->get());
  }
  public function page_db_query_capture(){
    
    exit ('sdfsdf');
    
    wfPlugin::includeonce('wf/array');
    $json = new PluginWfArray();
    $json->set('success', false);
    exit(json_encode($json->get()));
    
  }
  
  
  /**
   * Get yml.
   * Example $this->getYml('/page/desktop.yml');
   */
  private function getYmlzzz($file){
    return wfSettings::getSettingsAsObject('/plugin/wf/developerhelper/'.$file);
  }
  private function getYml($file = 'element/_some_file.yml'){
    wfPlugin::includeonce('wf/yml');
    return new PluginWfYml('/plugin/wf/developerhelper/'.$file);
  }
}
