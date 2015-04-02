<?php
namespace Molotov\Modules\Media\Controllers;
/*
 * The Activity Controller
 */
 
use Molotov\Core\Controllers\BaseController;
use Molotov\Modules\Media\Models\Repo;
use Molotov\Modules\Media\Models\Media;
use Molotov\Modules\Media\Models\Meta;
use Molotov\Modules\Media\Models\Folders;
 
class SearchController extends BaseController{

	public function action_index( $args ){
		$search = array(
			'limit' => 50,
			'offset' => 0,
			'order' => 'name'
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
		$search['conditions'] = ' 1 ';
		
		if(isset($args['query']) && !empty($args['query']) ){
			$search['conditions'] .= " AND name LIKE :query: OR path LIKE :query: ";
			$search['bind']['query'] = '%'.$args['query'].'%';
		}		
		if(isset($args['type']) && in_array(strtolower($args['type']),array('song','movie','picture','document','iso','subtitle','raw')) ){
			$search['conditions'] 	.= " AND type= :type: ";
			$search['bind']['type'] = $args['type'];
		}
		
		/*
		if(isset($args['artist']) && !empty($args['artist']) ){
			$search['conditions'] = "name LIKE :query: OR path LIKE :query:";
			$search['bind'] = array('query'=>'%'.$args['query'].'%' );
		}	
		*/	
		
		//echo "Search debug: ".print_r($search,1);
		$media = Media::find($search);
		$results = array('count'=>Media::count($search),'media'=>array());
		foreach( $media as $k=>$v ){
			$tmp = $v->serialize();
			foreach( $v->Meta as $meta ){
				$tmp['meta'][$meta->meta_key] = $meta->meta_value;
			}
			$results['media'][] = $tmp;
		}
		return $results;
	}

}