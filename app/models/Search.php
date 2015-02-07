<?php

class Search
{
	public $term;
	public $result = array();
	public $app;

	public function __construct($get, $app)
	{
		$this->setTerm($get['term']);
		$this->app = $app;
	}

	public function setTerm($term)
	{
		$this->term = trim(strip_tags($term));
	}

	public function search()
	{
		$this->user();
		$this->questionnaire();
		$this->group();
		$this->evaluation();
		
		return $this->result;
	}

	public function rpl($string)
	{
		$string = str_ireplace($this->term, '<b>'.$this->term.'</b>', $string);
		return $string;
	}

	public function url($entity, $item, $string = 'Ver')
	{
		return '<a href="'.$this->app->settings['urlbase_adm'].'/'.$entity.'/'.$item->id.'">'.$string.'</a>';
	}

	public function user()
	{
		$list = User::all(array(
			'conditions' => array(
				'name LIKE ? OR email LIKE ? OR username LIKE ?',
				'%'.$this->term.'%',
				'%'.$this->term.'%',
				'%'.$this->term.'%'
			)
		));

		foreach ($list as $item) {
			$data = array_filter(array(
				'url' => $this->url(__FUNCTION__, $item, User::$profile_types[$item->profile_type]),
				'name' => $this->rpl($item->name),
				'email' => $this->rpl($item->email),
				'username' => $this->rpl($item->username)
			));
			
			array_push($this->result, implode(' | ', array_values($data)));
		}

		return $list;
	}

	public function questionnaire()
	{
		$list = Questionnaire::all(array(
			'conditions' => array(
				'name LIKE ?',
				'%'.$this->term.'%'
			)
		));

		foreach ($list as $item) {
			$data = array_filter(array(
				'url' => $this->url(__FUNCTION__, $item, 'Questionário'),
				'name' => $this->rpl($item->name),
			));
			
			array_push($this->result, implode(' | ', array_values($data)));
		}

		return $list;
	}

	public function group()
	{
		$list = Group::all(array(
			'conditions' => array(
				'name LIKE ?',
				'%'.$this->term.'%'
			)
		));

		foreach ($list as $item) {
			$data = array_filter(array(
				'url' => $this->url(__FUNCTION__, $item, 'Grupo'),
				'name' => $this->rpl($item->name),
			));
			
			array_push($this->result, implode(' | ', array_values($data)));
		}

		return $list;
	}

	public function evaluation()
	{
		$list = Evaluation::all(array(
			'conditions' => array(
				'name LIKE ? OR subject LIKE ?',
				'%'.$this->term.'%',
				'%'.$this->term.'%'
			)
		));

		foreach ($list as $item) {
			$data = array_filter(array(
				'url' => $this->url(__FUNCTION__, $item, 'Avaliação'),
				'name' => $this->rpl($item->name),
				'subject' => $this->rpl($item->subject),
			));
			
			array_push($this->result, implode(' | ', array_values($data)));
		}

		return $list;
	}

	// public function index()
	// {
	// 	$index = Zend_Search_Lucene::create('/data/' . $this->table_name());
	// 	$doc = new Zend_Search_Lucene_Document();

	// 	$doc->addField(Zend_Search_Lucene_Field::Text('name', $docUrl));
	// 	$doc->addField(Zend_Search_Lucene_Field::UnStored('contents', $docContent));

	// 	$index->addDocument($doc);
	// }

	// public function after_save()
	// {
	// 	$index = Zend_Search_Lucene::open('/data/' . $this->table_name());
	// 	$doc = new Zend_Search_Lucene_Document();

	// 	$doc->addField(Zend_Search_Lucene_Field::Text('url', $docUrl));
	// 	$doc->addField(Zend_Search_Lucene_Field::UnStored('contents', $docContent));

	// 	$index->addDocument($doc);
	// }

	// public function after_destroy()
	// {

	// }
}