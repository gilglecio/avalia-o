<?php

class Model extends ActiveRecord\Model
{
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