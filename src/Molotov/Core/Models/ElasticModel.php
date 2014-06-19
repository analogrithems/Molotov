<?php

/*
 * This is the base model for objects stored in elasticsearch
 */

namespace Molotov\Core\Models;
  
abstract class ElasticModel extends BaseModel{

	/*
	 *  The ElasticSearch index to search within
	 */
	protected $index;

	/*
	 *  The ElasticSearch index to search within
	 */
	protected $type;
	protected $_client,$_index,$_type;
	
	protected $_id; //primary key of the record
	

		
	public function  __constructor($id = null, $options = null){
		if( $id && is_numeric( $id ) )
		{
			return $this->findByID($_id);
		}
	}
	
	/**
	* __getClient - This is how we start the communication with elastic Search
	*/
	protected function _getClient()
    {
        $this->client = $this->app->get('es');
    }
    
    /*
     * set this models elasitc search index
     */
	protected function _setIndex()
    {
        $this->_index = $this->client->getIndex($this->index);
    }
    
    /*
    * Set this models elasticsearch document type
    */
	protected function _setType()
    {
        $this->_type = $this->client->getType($this->type);
    }
 
	protected function _setup(){
		$this->_getClient();
		$this->_setIndex();
		$this->_setType();
	}

	 
	/*
	 * save - generic model save function to persist objects
	 */
	 
 	public function save($_id=null){
	 	$this->setup();
	 	if( !is_null($_id) ) $this->_id = $_id;//if we pass it set it
	 	$_model =  new \Elastica\Document($$this->_id, $this->serialize());
		// Add _model to type
		$elasticaType->addDocument($_model);
		
		// Refresh Index
		$elasticaType->getIndex()->refresh();
	 	
 	}
 	
 	/*
 	* findByID = get a single model returned by asking for it by id.
 	* either pass $_id as arg, or use class constructor with $_id
 	*/
 	public function findByID($_id=null){
	 	$this->setup();
	 	if( !is_null($_id) ) $this->_id = $_id;//if we pass it set it

	 	
 	}
 	
 	/*
 	 * delete - define how you delete models
 	 */
 	public function delete($_id=null){
	 	
 	}
 	

}