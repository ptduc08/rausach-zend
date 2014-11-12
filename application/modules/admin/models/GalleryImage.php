<?php
class Admin_Model_GalleryImage extends Zend_Db_Table {
	protected $_name = 'gallery_images';
	protected $_primary = 'id';
	
	public function changeStatus($arrParam = null, $options = null) {
		//----- truong hop thay doi thuoc tinh status
		if ($options['task'] == 'admin-newsarticle-change-status') {
			$cid = $arrParam['cid'];
			if (count($cid) > 0) {				//----- change status cho nhieu doi tuong
				switch ($arrParam['type']) {
					case 'active':
						$status = 1;
						break;
					case 'inactive':
						$status = 0;
						break;
				}
				$strId = implode(',', $cid);
				$data = array('status'=>$status);
				$where = 'id IN (' .$strId .')';
				$this->update($data, $where);
			}
			if (isset($arrParam['id'])) {		//----- change status cho mot doi tuong
				$id = $arrParam['id'];
				switch ($arrParam['type']) {
					case 'active':
						$status = 1;
						break;
					case 'inactive':
						$status = 0;
						break;
				}
				$data = array('status'=>$status);
				$where = 'id = ' .$id;
				$this->update($data, $where);
			}
		}
		
	}
	
	public function changeLockStatus($arrParam = null, $options = null) {
		//----- truong hop thay doi thuoc tinh status
		if ($options['task'] == 'admin-product-change-lock-status') {
			$cid = $arrParam['cid'];
			if (count($cid) > 0) {				//----- change lock status cho nhieu doi tuong
				switch ($arrParam['type']) {
					case 'active':
						$lock_status = 1;
						break;
					case 'inactive':
						$lock_status = 0;
						break;
				}
				$strId = implode(',', $cid);
				$data = array('lock_status'=>$lock_status);
				$where = 'id IN (' .$strId .')';
				$this->update($data, $where);
			}
			if (isset($arrParam['id'])) {		//----- change status cho mot doi tuong
				$id = $arrParam['id'];
				switch ($arrParam['type']) {
					case 'active':
						$lock_status = 1;
						break;
					case 'inactive':
						$lock_status = 0;
						break;
				}
				$data = array('lock_status'=>$lock_status);
				$where = 'id = ' .$id;
				$this->update($data, $where);
			}
		}
	
	}
	
	public function countItem($arrParam = null, $options = null) {
		$db = Zend_Registry::get('connectDb');
		$ssFilter = $arrParam['ssFilter'];
		$select = $db->select()
					->from('products AS p',array('COUNT(p.id) AS total'));
		
		//----- neu co tu khoa tim kiem thi bo sung them where cho select
		if (!empty($ssFilter['keywords'])) {
			$keywords = '%' .$ssFilter['keywords'] . '%';
			$select->where('p.product_name LIKE ?',$keywords);
			
		}
		
		$result = $db->fetchOne($select);
		return $result;
	}
	
	public function deleteItem($arrParam = null, $options = null) {
		//----- truong hop xoa mot doi tuong -------------------------------------------------------------
		if ($options['task'] == 'admin-galleryimage-delete') {
			//----- goi getItem de lay ten gallery_image cua image dang bi xoa
			//----- va tien hanh xoa gallery_image khoi he thong
			$gallery_image = $this->getItem($arrParam,array('task'=>'admin-get-deleting-galleryimage'));
			$upload_dir = FILES_PATH . '/gallery/gallery-images';
			$upload = new Zendvn_File_Upload();
			$upload->removeFile($upload_dir . '/small/' . $gallery_image);
			$upload->removeFile($upload_dir . '/medium/' . $gallery_image);
			$upload->removeFile($upload_dir . '/large/' . $gallery_image);
			$upload->removeFile($upload_dir . '/original/' . $gallery_image);
			
			//----- sau khi xoa het user avatar tien hanh xoa article khoi database
			$where = ' id = ' .$arrParam['id'];
			$this->delete($where);
			
		}
		
		//----- truong hop xoa nhieu doi tuong -------------------------------------------------------------
		if ($options['task'] == 'admin-galleryimage-multi-delete') {
			$cid = $arrParam['cid'];
			if (count($cid) > 0) {
				//----- dau tien phai xoa gallery_image cua cac image se bi delete
				foreach ($cid as $key=>$val) {
					//----- truyen id cua image bi xoa vao mang $arrParam
					$arrParam['id'] = $val;
					//----- goi getItem de lay ten gallery_image cua image dang bi xoa
					//----- va tien hanh xoa gallery_image khoi he thong
					$gallery_image = $this->getItem($arrParam,array('task'=>'admin-get-deleting-galleryimage'));
					$upload_dir = FILES_PATH . '/gallery/gallery-images';
					$upload = new Zendvn_File_Upload();
					$upload->removeFile($upload_dir . '/small/' . $gallery_image);
					$upload->removeFile($upload_dir . '/medium/' . $gallery_image);
					$upload->removeFile($upload_dir . '/large/' . $gallery_image);
					$upload->removeFile($upload_dir . '/original/' . $gallery_image);
					
					//----- sau khi xoa het user avatar tien hanh xoa article khoi database
					$where = ' id = ' .$arrParam['id'];
					$this->delete($where);
				}

			}
		}
	}
	
	public function getItem($arrParam = null, $options = null) {
		$db = Zend_Registry::get('connectDb');
		//$db = Zend_Db::factory($adapter,$config);
		//----- lay thong tin cua mot image khi biet id cua image -------------------------------------------
		if ($options['task'] == 'admin-galleryimage-info') {
			$select = $db->select()
						 ->from('gallery_images AS gi')
						 ->joinLeft('gallery AS g','g.id = gi.gallery_id','g.gallery_name')
						 ->joinLeft('users AS u', 'gi.created_id = u.id',array('u.user_name AS created_name'))
						 ->joinLeft('users AS u3', 'gi.publisher_id = u3.id',array('u3.user_name AS publisher_name'))
						 ->where('gi.id = ?',$arrParam['id'],INTEGER)
						 ->group('gi.id');			
			
			$result = $db->fetchRow($select);
		}
		
		//----- lay ten gallery_image cua image dang bi xoa de xoa gallery_image -----------------------------------
		if ($options['task'] == 'admin-get-deleting-galleryimage') {
			$db = Zend_Registry::get('connectDb');
			$select = $db->select()
						 ->from('gallery_images AS gi',array('gi.gallery_image'))
						 ->where('gi.id = ?',$arrParam['id'],INTEGER);
			$result = $db->fetchOne($select);
		}
		
		//----- lay order lon nhat trong bang gallery_images -----------------------------------------------------
		if ($options['task'] == 'admin-get-biggest-image-order') {
			$db = Zend_Registry::get('connectDb');
			//$db = Zend_Db::factory($adapter,$config);
			$select = $db->select()
						 ->from('gallery_images AS gi','gi.order')
						 ->order('gi.order DESC');
			$result = $db->fetchOne($select);
		}
		
		//----- lay lock_status cua gallery image duoc chon ------------------------------------------------------------
		if ($options['task'] == 'admin-galleryimage-get-lock-status') {
			$db = Zend_Registry::get('connectDb');
			//$db = Zend_Db::factory($adapter,$config);
			if (isset($arrParam['cid']) && count($arrParam['cid'] > 0)) {
				//----- truong hop lay lock_status cua nhieu doi tuong
				$arrLockStatus = array();
				$arrCid = $arrParam['cid'];
				$select = $db->select()
							 ->from('gallery_images AS gi','gi.lock_status')
							 ->where('gi.id IN (?)',$arrCid);
				$result = $db->fetchAll($select);
		
			} else if (isset($arrParam['id'])) {
				//----- truong hop lay lock_status cua mot doi tuong
				$select = $db->select()
							 ->from('gallery_images AS gi','gi.lock_status')
							 ->where('gi.id = ?',$arrParam['id'],INTEGER);
				$result = $db->fetchOne($select);
			}
				
		}
		
		//----- lay ten cover_image cua product dang bi xoa de xoa cover_image
		if ($options['task'] == 'admin-get-deleting-product-coverimage') {
			$db = Zend_Registry::get('connectDb');
			$select = $db->select()
						 ->from('products AS p',array('p.cover_image'))
						 ->where('p.id = ?',$arrParam['id'],INTEGER);
			$result = $db->fetchOne($select);
		}
		
		//----- lay order lon nhat trong bang service
		if ($options['task'] == 'admin-get-biggest-product-order') {
			$db = Zend_Registry::get('connectDb');
			//$db = Zend_Db::factory($adapter,$config);
			$select = $db->select()
						 ->from('products AS p','p.order')
						 ->order('p.order DESC');
			$result = $db->fetchOne($select);
		}
		
		return $result;
	}
	
	public function listItem($arrParam = null, $options = null) {
		$db = Zend_Registry::get('connectDb');
		
		if ($options['task'] == 'admin-image-list') {
			$paginator = $arrParam['paginator'];
			$ssFilter = $arrParam['ssFilter'];
			//$db = Zend_Db::factory($adapter,$config);
			$select = $db->select()
						 ->from('gallery_images AS gi')
						 ->joinLeft('gallery AS g','g.id = gi.gallery_id','g.gallery_name')
						 ->group('gi.id');

			//----- neu co thong so sap xep thi bo sung order de sap xep
			if (!empty($ssFilter['col']) && !empty($ssFilter['col'])) {
				$select->order($ssFilter['col'] .' ' . $ssFilter['order']);
			}
			
			//----- neu co thong so phan trang thi bo sung limitPage cho select
			if ($paginator['itemCountPerPage'] > 0) {
				$currentPage = $paginator['currentPage'];
				$itemCountPerPage = $paginator['itemCountPerPage'];
				$select->limitPage($currentPage, $itemCountPerPage);
			}
			
			//----- neu co tu khoa tim kiem thi bo sung where cho select
			if (!empty($ssFilter['keywords'])) {
				$keywords = '%' .$ssFilter['keywords'] . '%';
				$select->where('gi.gallery_name LIKE ?',$keywords);
			}
			
			//----- neu co thong so loc theo gallery_id thi bo sung where cho select
			if ($ssFilter['gallery_id'] > 0) {
				$select->where('gi.gallery_id = ?',$ssFilter['gallery_id'],INTEGER);
			}
			
			$result = $db->fetchAll($select);
			
		}
		
		//----- lay danh sach cac image co id nam trong mot mang (mang $arrParam['cid'])
		if ($options['task'] == 'admin-galleryimage-list-from-array') {
			$cid = $arrParam['cid'];
			$select = $db->select()
						 ->from('gallery_images AS gi',array('gi.id','gi.image_title','gi.gallery_image'))
						 ->where('gi.id IN (?)',$cid);
			$result = $db->fetchAll($select);
		}
		
		//----- lay id va category_name cua tat ca cac nhom tin tuc (phuc vu cho selectbox)
		if ($options['task'] == 'admin-newscategory-list-selectbox') {
			$select = $db->select()
						 ->from('news_category',array('id','category_name'));
			$result = $db->fetchPairs($select);
			$result[0] = '-- Select a Category --';
			//----- sap xep lai mang $result theo khoa
			ksort($result);
		}
		
		//----- lay id va category_name cua cac nhom tin tuc, tru nhom tin tuc hien tai (phuc vu cho selectbox)
		if ($options['task'] == 'admin-newscategory-list-selectbox-parent-category') {
			//----- lay id cua nhom tin tuc dang duoc chon
			if (isset($arrParam['id'])) {
				$current_category_id = $arrParam['id'];
			} else {
				$current_category_id = 0;
			}
			
			$select = $db->select()
						 ->from('news_category',array('id','category_name'))
						 ->where('id != ?',$current_category_id,INTEGER);
			$result = $db->fetchPairs($select);
			$result[0] = '-- Select a Category --';
			//----- sap xep lai mang $result theo khoa
			ksort($result);
		}
		
		//----- lay danh sach cac category cua mot tin tuc, khi biet id cua tin tuc do
		if ($options['task'] == 'admin-newsarticle-list-category') {
			$article_id = $arrParam['article_id'];
			$select = $db->select()
						 ->from('news_category_article AS nca','nca.category_id')
						 ->joinLeft('news_category AS nc', 'nc.id = nca.category_id','nc.category_name')
						 ->where('nca.article_id = ?', $article_id, INTEGER);
			$result = $db->fetchAll($select);
		}
		
		//----- lay danh sach cac category ID cua mot tin tuc, khi biet id cua tin tuc do
		if ($options['task'] == 'admin-newsarticle-list-category-only-id') {
			$article_id = $arrParam['article_id'];
			//$db = Zend_Db::factory($adapter,$config);
			$select = $db->select()
						 ->from('news_category_article AS nca','nca.category_id')
						 ->where('nca.article_id = ?', $article_id, INTEGER);
			$tmp = $db->fetchAll($select);
			$result = array();
			if (count($tmp) > 0) {
				foreach ($tmp as $key=>$val) {
					$result[] = $val['category_id'];
				}
			}
		}
		
		return $result;
	}
	
	public function publish($arrParam = null, $options = null) {
		if (isset($arrParam['cid'])) {
			$cid = $arrParam['cid'];
		} else {
			$cid = array();
		}
	
		if (count($cid) > 0) {				//----- publish cho nhieu doi tuong
			switch ($arrParam['type']) {
				case 'active':
					$publish = 1;
					break;
				case 'inactive':
					$publish = 0;
					break;
			}
			$strId = implode(',', $cid);
			$data = array('publish'=>$publish);
			$where = 'id IN (' .$strId .')';
			$this->update($data, $where);
				
			$this->saveItem($arrParam,array('task'=>'admin-galleryimage-publish-multi-product'));
		}
		if (isset($arrParam['id'])) {		//----- publish cho mot doi tuong
			$id = $arrParam['id'];
			switch ($arrParam['type']) {
				case 'active':
					$publish = 1;
					break;
				case 'inactive':
					$publish = 0;
					break;
			}
			$data = array('publish'=>$publish);
			$where = 'id = ' .$id;
			$this->update($data, $where);
				
			$this->saveItem($arrParam,array('task'=>'admin-galleryimage-publish-one-product'));
		}
	
	}
	
	public function saveItem($arrParam = null, $options = null) {
		if ($options['task'] == 'admin-image-add') {
			$row 				= $this->fetchNew();
			
			$row->gallery_id	= $arrParam['gallery_id'];
			$row->image_title	= $arrParam['image_title'];
			$row->gallery_image	= $arrParam['gallery_image'];
			$row->publish	 	= $arrParam['publish'];
			$row->order		 	= $arrParam['order'];
			
			//----- lay user_id cua user upload anh
			$info = new Zendvn_System_Info();
			$user_id = $info->getMemberInfo('id');
			$row->created_id 	= $user_id;
			$row->created_time	= date("Y-m-d H:m:s");
			
			if ($arrParam['publish'] == 0) {
				//----- nguoi soan bai khong publish bai viet, hoac khong co quyen publish bai viet
				$row->publisher_id	= 0;
				$row->publish_time	= '0000-00-00 00:00:00';
			} else {
				//----- nguoi soan bai publish luon bai viet thi cap nhat luon thong tin nguoi va thoi gian publish
				$row->publisher_id	= $user_id;;
				$row->publish_time	= date("Y-m-d H:m:s");
			}
			
			$row->save();
			
		}
		
		if ($options['task'] == 'admin-product-edit') {
			$where 				= ' id = ' . $arrParam['id'];
			$row 				= $this->fetchRow($where);
			
			$row->product_category_id	= $arrParam['product_category_id'];
			$row->product_name	= $arrParam['product_name'];
			$row->product_serial	= $arrParam['product_serial'];
			$row->price			= $arrParam['price'];
			$row->product_detail	= $arrParam['product_detail'];
			$row->cover_image	= $arrParam['cover_image'];
			$row->publish	 	= $arrParam['publish'];
			$row->order		 	= $arrParam['order'];
			$row->lock_status 	= $arrParam['lock_status'];
			
			//----- lay user_id cua user dang login vao he thong
			$info = new Zendvn_System_Info();
			$user_id = $info->getMemberInfo('id');
			$row->last_modified_id 	= $user_id;
			$row->last_modified_time = date("Y-m-d H:m:s");

			$row->save();

		}
		
		if ($options['task'] == 'admin-galleryimage-publish-one-product') {
			$where 				= ' id = ' . $arrParam['id'];
			$row 				= $this->fetchRow($where);
			//----- lay user_id cua user dang login vao he thong
			$info = new Zendvn_System_Info();
			$user_id = $info->getMemberInfo('id');
			$row->publisher_id 	= $user_id;
			$row->publish_time = date("Y-m-d H:m:s");
			$row->save();
		}
		
		if ($options['task'] == 'admin-galleryimage-publish-multi-product') {
			$cid = $arrParam['cid'];
			if (count($cid) > 0) {
				foreach($cid as $key=>$val) {
					$procat_id 		= $val;
					$where 				= ' id = ' . $procat_id;
					$row 				= $this->fetchRow($where);
					//----- lay user_id cua user dang login vao he thong
					$info = new Zendvn_System_Info();
					$user_id = $info->getMemberInfo('id');
					$row->publisher_id 	= $user_id;
					$row->publish_time = date("Y-m-d H:m:s");
					$row->save();
				}
			} 
		}
	}
	
	public function sortItem($arrParam = null, $options = null) {
		/* if (isset($arrParam['cid'])) {
			$cid = $arrParam['cid'];
		} else {
			$cid = array();
		} */
		
		$order = $arrParam['order'];
		if (count($order) > 0) {
			foreach ($order as $key=>$value) {
				$where = 'id = ' .$key;
				$data = array('order'=>$value);
				$this->update($data,$where);
			}
		}
		
	}
}