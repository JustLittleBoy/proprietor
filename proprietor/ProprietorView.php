<?php
namespace proprietor;
class ProprietorView
{
	// extract
	
	//模版中的数据
	protected $Vdata = array();
	
	//模板内容
	protected $_content='';
	
	protected $Param=array();
	//当前使用的模板
	protected $theme    =   '';

	public function __construct()
	{
		$this->theme=VIEW_PATH;
	}
	
	//添加数据
	public function assign($name,$val){
		$this->Vdata[$name]=$val;
		return $this;
	}
	
	
	//获取模板
	public function fetch($view_path){
		if(!$view_path){
			$view_path=strtolower(NOWCLASS).'/'.ACTION.VIEWPOSTFIX;
		}
		
		if(substr($view_path,0,1)=="/"){
			$view_path=substr($view_path,0,1);
		}
		//html文件路径
		$this->theme=$this->theme.$view_path;
		
		//拆分控制器和模板
		$view_array=explode('/',$view_path);
		
		//生成对应的存放路径
		if(count($view_array)>1){
			$cache_name=$view_array[1];
			$cache_file=CACHE_PATH .$view_array[0].'/';
		}else{
			$cache_name=$view_array[0];
			$cache_file=CACHE_PATH;
		}
		
		//获得对应的文件名
		$cache_name=md5($this->theme).$cache_name;
		
		//php文件对应的完整文件路径和文件
		$this->Param['proprietor_cache_php']=$cache_file .str_replace('html','php',$cache_name);

		//html文件对应的完整文件路径和文件
		$this->Param['proprietor_cache_html']=$cache_file.$cache_name;
		
		
		//按照时间来判断是否需要伪静态，如果修改过就重新渲染，否则，直接读取html
		
		if(defined('CACHE_TIME') &&is_file($this->Param['proprietor_cache_php'])&& (time()-filemtime($this->Param['proprietor_cache_php']))<=(CACHE_TIME)){
				$this->_content=file_get_contents($this->Param['proprietor_cache_html']);
		}else{
			//启用缓存
			ob_start();
			
			if(!file_exists($this->theme)){
				\ProprietorTool::showSysErrorPage('模板文件不存在['.$this->theme.']');
			}
			
			$this->_content = file_get_contents($this->theme);
			
			if(!file_exists($cache_file)){
				mkdir($cache_file,'0777',true) ;
			}
			
			$this->_content=$this->analysis($this->_content);
			
			file_put_contents($this->Param['proprietor_cache_php'], $this->_content);
			
			ob_clean();
			
			//覆盖变量
			extract($this->Vdata,EXTR_OVERWRITE);

			include $this->Param['proprietor_cache_php'];
			
			$this->_content = ob_get_clean(); // 获得运行结果
			 
			ob_end_clean();
			
			file_put_contents($this->Param['proprietor_cache_html'], $this->_content); // 生成纯文本，处理缓存目录
			
		}
		
		return $this;
	}
	
	
	//显示模板
	public function display($view_path=''){
		$this->fetch($view_path);
		echo $this->_content;
	}
	
	//解析模板标签文件
	public function analysis($content){
		$content=$this->analysis_view($content);
		return $content;
	}
	
	/**
	 * 解析模板引入
	 * { include_view(view.html|index/index.html) }}
	 * @param  $content
	 * @return $content
	 */
	public function analysis_view($content){
		preg_match_all('/\{\{ (include_view)\((.*)\) \}\}/',$content,$result);
		foreach ($result[1] as $key=>$item){
			if($item=='include_view'){
				$string=file_get_contents(VIEW_PATH.$result[2][$key]);
				$content=str_replace($result[0][$key],$string,$content);
			}
		}
		return $content;
	}

}