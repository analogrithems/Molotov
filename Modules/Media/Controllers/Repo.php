<?php
namespace Media\Controllers;
/*
 * The Repo Controller
 */
 
use Molotov\Core\Controllers\BaseController;
 
class Repo extends BaseController{

	public function action_create( $args ){
		if( \Media\Models\Repo::count(
			array(
				"name = :name: OR path = :path:",'
				bind'=>array(
					'name'=>$args['name'],
					'path'=>$args['path']
				)
			)) > 0){
			throw new \Exception('Repo Already Exists');
			$repo = new Repo();
			$repo->name = $args['name'];
			$repo->path = $args['path'];			 
			$repo->save();
		}
	}
	
	public function action_index( $args ){
		$search = array(
			'limit' => 50,
			'offset' => 0,
			'order' => 'name',
			'conditions'=>" enabled=1 "
		);
		if(isset($args['limit']) && !empty($args['limit'])) $search['limit'] = $args['limit'];
		if(isset($args['offset']) && !empty($args['offset'])) $search['offset'] = $args['offset'];
		if(isset($args['sortfield']) && !empty($args['sortfield'])) $search['sort'] = $args['sortfield'];
		if(isset($args['sortdir']) && !empty($args['sortdir'])){
			if( preg_match('/asc/i',$args['sortdir']) > 0 ){
				$search['sort'] .= ' ASC';
			}elseif( preg_match('/desc/i',$args['sortdir']) > 0 ){
				$search['sort'] .= ' desc';
			}
		}
		
		if(isset($args['query']) && !empty($args['query']) ){
			$search['conditions'] .= " AND name LIKE :query: OR path LIKE :query:";
			$search['bind'] = array('query'=>'%'.$args['query'].'%' );
		}
		
		$repos = \Media\Models\Repo::find($search);
		$results = array(
			'total'=>count($repos),
			'status'=>'ok',
			'repos'=>array()
		);
		foreach( $repos as $k=>$v ){
			$tmp = array('id'=>$v->id,'name'=>$v->name);
			$results['repos'][] = $tmp;
		}
		return $results;
	}
	

	public function action_delete( $id ){
		return \Media\Models\Repo::findFirst($id)->delete();
	}
}