<?php
namespace support\models;

use \PDO;

class Support{
	private $session_key = "";
	public $db;
	private $site_key;

    public function __construct($db_connection=false,$session_key=false,$site_key=false){
		if($db_connection && is_object($db_connection)){
			$this->db = $db_connection;
		}
		$this->session_key = $session_key;
		$this->site_key = $site_key;
    }
	
	public function validate_support_form($post,$files,$file_validation){
		$error_array = array();
		//for best practice, don't assume these classes are present
		if(!class_exists("Validation")){
			include($_SERVER['PATH_TO_CORE'] . PATH_TO_CLASSES . "/query_builder.class.php");
			include($_SERVER['PATH_TO_CORE'] . PATH_TO_CLASSES . "/validation.class.php");
		}		
		//need to collect personal info
		if(isset($post['first_name']) && $post['first_name']){
			
			if(!Validation::verify($post['first_name'],"text")){
				//it isn't text, throw error
				$error_array["first_name"] = "First name is invalid";
			}
		}else{
			if(!isset($_SESSION[$this->session_key])){
				
				//they are not logged in, so throw an error
				$error_array["first_name"] = "First name is missing";
			}
		}
		
		if(isset($post['last_name']) && $post['last_name']){
			if(!Validation::verify($post['last_name'],"text")){
				$error_array["last_name"] = "Last name is invalid";
			}
		}else{
			if(!isset($_SESSION[$this->session_key])){
				$error_array["last_name"] = "Last name is missing";
			}
		}
		
		if(isset($post['phone']) && $post['phone']){
			if(!Validation::verify($post['phone'],"telephone")){
				$error_array["phone"] = "Phone number is invalid";
			}
		}
		
		if(isset($post['email']) && $post['email']){
			if(!Validation::verify($post['email'],"email")){
				$error_array["email"] = "Email address is invalid"; 
			}
		}else{
			if(!isset($_SESSION[$this->session_key])){
				$error_array["email"] = "Email address is missing";
			}
		}
		
		if(isset($post['support_category_id']) && $post['support_category_id']){
			if(!Validation::verify($post['support_category_id'],"int")){
				$error_array["support_category_id"] = "Subject is invalid";
			}
		}else{
			$error_array["support_category_id"] = "Subject is missing";
		}
		
		if(isset($post['title']) && $post['title']){
			if(!Validation::verify($post['title'],"text")){
				$error_array["title"] = "Title is invalid";
			}
		}else{
			$error_array["title"] = "Title is missing";
		}
		
		if(isset($post['body']) && $post['body']){
			if(!Validation::verify($post['body'],"text")){
				$error_array["body"] = "Body is invalid";
			}
		}else{
			$error_array["body"] = "Body is missing";
		}
		
		if(!isset($_SESSION[$this->session_key])){
			//we need to check for the captcha
			if(isset($post['captcha'])){
				if(strtolower($post['captcha']) != strtolower($_SESSION['support_captcha'])){
					$error_array["captcha"] = "Captcha is invalid";
				}
			}else{
				$error_array["captcha"] = "Captcha is missing";
			}
		}
		
		if(isset($files['support_file'])){
			if(!in_array($files['support_file']['type'],$file_validation['allowed_mime_types'])){
				$error_array["support_file"] = "File type is invalid: " . $files['support_file']['type'];
			}
			if($files['support_file']['size'] > $file_validation['max_size']){
				$error_array["support_file"] = "File is too large";
			}
		}
		
		return $error_array;
	}
	
	public function insert_support($post,$files){
		//get the site id
		$statement = $this->db->prepare("
	    	SELECT *
	    	FROM support_site
	    	WHERE site_name = :site_name");
		$statement->bindValue(":site_name", $this->site_key, PDO::PARAM_STR);
        $statement->execute();
		$site_data = $statement->fetch(PDO::FETCH_ASSOC); 
		$site_id = $site_data['support_site_id'];
		
		$statement = $this->db->prepare("
	    	INSERT INTO support_request
	    		(account_id
	    		,support_site_id
	    		,first_name
				,last_name
				,phone
				,email
				,support_category_id
				,title
				,body
				,created_date
				,active)
	    	VALUES
	    		(:account_id
	    		,:support_site_id
	    		,:first_name
				,:last_name
				,:phone
				,:email
				,:support_category_id
				,:title
				,:body
				,NOW()
				,1)");
		$requestor_email = isset($_SESSION[$this->session_key]) ? $_SESSION[$this->session_key]['email'] : $post['email'];
		$requestor_first_name = isset($_SESSION[$this->session_key]) ? $_SESSION[$this->session_key]['given_name'] : $post['first_name'];
		$requestor_last_name = isset($_SESSION[$this->session_key]) ? $_SESSION[$this->session_key]['sn'] : $post['last_name'];
		$statement->bindValue(":account_id", isset($_SESSION[$this->session_key]) ? $_SESSION[$this->session_key]['account_id'] : null, PDO::PARAM_INT);
		$statement->bindValue(":support_site_id", $site_id, PDO::PARAM_INT);
		$statement->bindValue(":first_name", $requestor_first_name, PDO::PARAM_STR);
		$statement->bindValue(":last_name", $requestor_last_name, PDO::PARAM_STR);
		$statement->bindValue(":phone", isset($_SESSION[$this->session_key]) ? @$_SESSION[$this->session_key]['telephonenumber'] : $post['phone'], PDO::PARAM_STR);
		$statement->bindValue(":email", $requestor_email, PDO::PARAM_STR);
		$statement->bindValue(":support_category_id", $post['support_category_id'], PDO::PARAM_INT);
		$statement->bindValue(":title", $post['title'], PDO::PARAM_STR);
		$statement->bindValue(":body", $post['body'], PDO::PARAM_STR);
        $statement->execute();
		$support_request_id = $this->db->lastInsertId();
		
		if($files){
			$statement = $this->db->prepare("
		    	INSERT INTO support_file
		    		(support_request_id
		    		,file_name
		    		,file_type
		    		,file_content
		    		,file_size)
		    	VALUES
		    		(:support_request_id
		    		,:file_name
		    		,:file_type
		    		,:file_content
		    		,:file_size)");
			$file_content = fopen($files['support_file']['tmp_name'],'rb');
			$statement->bindValue(":support_request_id", $support_request_id, PDO::PARAM_INT);
			$statement->bindValue(":file_name", $files['support_file']['name'], PDO::PARAM_STR);
			$statement->bindValue(":file_type", $files['support_file']['type'], PDO::PARAM_STR);
			$statement->bindParam(":file_content", $file_content, PDO::PARAM_LOB);
			$statement->bindValue(":file_size", $files['support_file']['size'], PDO::PARAM_INT);
	        $statement->execute();
		}
		
		//mail out the support to requestor and to the admin
		$configuration = $this->get_configuration();
		$category_info = $this->get_categories($post['support_category_id']);
		$file_name = (!empty($files['support_file']['name'])) ? $files['support_file']['name'] : "N/A";
		
		if($configuration['confirmation_email']){
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= "From: " . $configuration['email_from'] . "\r\n";
			$mail_body = "
			<html>
	        <head>
	        <title>Support Ticket Submitted</title>
	        </head>
	        <body style='margin:20px;'>"
	        . $configuration['email_body'] .
			"<br />
			Submitter: " . $requestor_first_name . " " . $requestor_last_name . "
	        <br />
	        <br />
	        Title: {$post['title']}
	        <br />
	        <br />
	        Body: {$post['body']}
	        <br />
	        <br />
	        Category: " . $category_info[0]['category_name'] . "
	        <br />
	        <br />
	        File: " . $file_name . "
	        <br />
	        <br />" 
			. $configuration['email_signature']
			."</body></html>";
			mail($requestor_email,$configuration['email_subject'],$mail_body,$headers);
		}
		
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "From: " . $requestor_email . "\r\n";
		$mail_body = "
		<html>
        <head>
        <title>Support Ticket Submitted</title>
        </head>
        <body style='margin:20px;'>
        <div style='margin-bottom:50px;'>
        <p>" . strtoupper($this->site_key) . " Admin,</p>
        <p>A support ticket as been submitted.  Following is the ticket information:
	        <br />
	        <br />
	        Submitter: " . $requestor_first_name . " " . $requestor_last_name . "
	        <br />
	        <br />
	        Title: {$post['title']}
	        <br />
	        <br />
	        Body: {$post['body']}
	        <br />
	        <br />
	        Category: " . $category_info[0]['category_name'] . "
	        <br />
	        <br />
	        File: " . $file_name . "
	        <br />
	        <br />
	        Please take the time to review this ticket at your earliest convenience.  To view this ticket, visit the following link: <a href='http://{$_SERVER['HTTP_HOST']}/support/details/{$support_request_id}'>http://{$_SERVER['HTTP_HOST']}/support/details/{$support_request_id}</a></p>
        </div>
        </body>
        </html>
		";
		mail($configuration['admin_emails'],"Support Ticket",$mail_body,$headers);
	}
	
	public function get_categories($category_id=false){
		$category_id_sql = "";
		if($category_id){
			$category_id_sql = " AND support_category_id = :support_category_id ";
		}
		$statement = $this->db->prepare("
	    	SELECT support_category_id
	    		   ,category_name
	    		   ,site_id
	    		   ,DATE_FORMAT(created_date,'%m/%d/%Y') AS created_date
	    	FROM support_category
	    	LEFT JOIN support_site ON support_site.support_site_id = support_category.site_id
	    	WHERE support_site.site_name = :site_name
	    	{$category_id_sql}");
		$statement->bindValue(":site_name", $this->site_key, PDO::PARAM_STR);
		if($category_id){
			$statement->bindValue(":support_category_id", $category_id, PDO::PARAM_INT);
		}
        $statement->execute();
		$category_data = $statement->fetchAll(PDO::FETCH_ASSOC);
		return $category_data;
	}
	
	public function verify_site(){
		$statement = $this->db->prepare("
	    	SELECT *
	    	FROM support_site
	    	WHERE site_name = :site_name");
		$statement->bindValue(":site_name", $this->site_key, PDO::PARAM_STR);
        $statement->execute();
		$site_data = $statement->fetch(PDO::FETCH_ASSOC); 
		if(!$site_data){
			//if the site doesn't exist yet, enter it in
			$statement = $this->db->prepare("
		    	INSERT INTO support_site
		    		(site_name)
		    	VALUES
		    		(:site_name)");
			$statement->bindValue(":site_name", $this->site_key, PDO::PARAM_STR);
	        $statement->execute();
		}
	}
	
	public function browse_support($sort_field = false,$sort_order = 'DESC',$start_record = 0, $stop_record = 20, $search = false,$column_filters = false, $sortable_fields = false, $site_key = false){
		$sort = ""; 
	    $search_sql = "";
	    $column_filter_sql = "";
	    $pdo_params = array();
		$pdo_params[] = $this->site_key;
		
		$limit_sql = " LIMIT {$start_record}, {$stop_record} ";
    
		if($sort_field){
		  switch($sort_field){
		    case 'last_modified':
		      $sort = " ORDER BY support.last_modified {$sort_order} ";
		      break;
		    default:
		      $sort = " ORDER BY {$sort_field} {$sort_order} "; 
	    	}
		}
		
		if($search) {
		  $pdo_params[] = '%'.$search.'%';
		  $pdo_params[] = '%'.$search.'%';
		  $pdo_params[] = '%'.$search.'%';
		  $pdo_params[] = '%'.$search.'%';
			$search_sql = " 
				AND (
					support_request.last_name LIKE ?
					OR support_request.first_name LIKE ?
					OR support_request.title LIKE ?
					OR support_request.body LIKE ?
				) ";
		}
		
		$comparison_array = array(
			"gt" => " > "
		  	,"gt_or_eq" => " >= "
		  	,"lt" => "<"
		  	,"lt_or_eq" => " <= "
  			,"equals" => " = "
  			,"contains" => "contains"
  			,"not_contain" => "not_contain"
  			,"start_with" => "start_with"
  			,"end_with" => "end_with"
		);
		
		if($column_filters){
			$column_filter_array = array();
  			foreach($column_filters as $filter) {
    			$params = $filter;
    			if(is_object($filter)){
      				$params = get_object_vars($filter);
    			}
    		    		
    			if(isset($params['value']) 
    		  		&& $params['value'] 
    		  		&& isset($params['comparison']) 
    		  		&& $params['comparison'] 
    		  		&& isset($comparison_array[$params['comparison']]) 
    		  		&& $comparison_array[$params['comparison']] 
    		  		&& isset($params['column'])
    		  		&& array_key_exists($params['column'], $sortable_fields)) // $sortable_fields -- insuring against SQL injection
    			{
    		
      				switch($params['comparison']){
        				case "contains": //contains
    						$comparison_and_value = " LIKE ? ";
    						$pdo_params[] = "%".$params['value']."%";
    					break;
    					case "not_contains": //does not contain
    						$comparison_and_value = " NOT LIKE ? ";
    						$pdo_params[] = "%".$params['value']."%";
    					break;
    					case "start_with": //starts with
    						$comparison_and_value = " LIKE ? ";
    						$pdo_params[] = $params['value']."%";
    					break;
    					case "end_with": //ends with
    						$comparison_and_value = " LIKE ? ";
    						$pdo_params[] = "%".$params['value'];
    					break;
    					default:
    				  		$comparison_and_value = $comparison_array[$params['comparison']]." ? ";
    				  		$pdo_params[] = $params['value'];
      				}
					
      				$column_filter_array[] = " ({$params['column']} {$comparison_and_value}) ";
    			}
  			}
  		
  			if($column_filter_array){
    			$column_filter_sql = " AND (".implode(' AND ',$column_filter_array).") ";
  			}
		}
		
		$statement = $this->db->prepare("
    		SELECT SQL_CALC_FOUND_ROWS
    			   support_request.support_request_id AS manage
    			   ,CONCAT(support_request.first_name, ' ', support_request.last_name) AS submitter
    			   ,support_request.title
    			   ,support_request.body
    			   ,DATE_FORMAT(support_request.created_date,'%m/%d/%Y') AS created_date
    			   ,support_category.category_name AS category
    			   ,support_request.support_request_id AS DT_RowId
    		FROM core_framework.support_request
    		LEFT JOIN support_category ON support_category.support_category_id = support_request.support_category_id
    		LEFT JOIN support_file ON support_file.support_request_id = support_request.support_request_id
    		LEFT JOIN support_site ON support_site.support_site_id = support_request.support_site_id
    		WHERE support_site.site_name = ?
    		AND active = 1
    		{$search_sql}
    		GROUP BY support_request.support_request_id
			HAVING 1=1
			{$column_filter_sql}
      		{$sort}
			{$limit_sql}");
		$statement->execute($pdo_params);
		$data["aaData"] = $statement->fetchAll(PDO::FETCH_ASSOC);
	  	$statement = $this->db->prepare("SELECT FOUND_ROWS()");
  		$statement->execute();
  		$count = $statement->fetch(PDO::FETCH_ASSOC);
		$data["iTotalRecords"] = $count["FOUND_ROWS()"];
		$data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];
		return $data;
	}
	
	public function browse_support_categories($sort_field = false,$sort_order = 'DESC',$start_record = 0, $stop_record = 20, $search = false,$column_filters = false, $sortable_fields = false, $site_key = false){
		$sort = ""; 
	    $search_sql = "";
	    $column_filter_sql = "";
	    $pdo_params = array();
		$pdo_params[] = $this->site_key;
		
		$limit_sql = " LIMIT {$start_record}, {$stop_record} ";
    
		if($sort_field){
		  switch($sort_field){
		    case 'last_modified':
		      $sort = " ORDER BY support_category.last_modified {$sort_order} ";
		      break;
		    default:
		      $sort = " ORDER BY {$sort_field} {$sort_order} "; 
	    	}
		}
		
		if($search) {
		  $pdo_params[] = '%'.$search.'%';
			$search_sql = " 
				AND (
					support_category.category_name LIKE ?
				) ";
		}
		
		$comparison_array = array(
			"gt" => " > "
		  	,"gt_or_eq" => " >= "
		  	,"lt" => "<"
		  	,"lt_or_eq" => " <= "
  			,"equals" => " = "
  			,"contains" => "contains"
  			,"not_contain" => "not_contain"
  			,"start_with" => "start_with"
  			,"end_with" => "end_with"
		);
		
		if($column_filters){
			$column_filter_array = array();
  			foreach($column_filters as $filter) {
    			$params = $filter;
    			if(is_object($filter)){
      				$params = get_object_vars($filter);
    			}
    		    		
    			if(isset($params['value']) 
    		  		&& $params['value'] 
    		  		&& isset($params['comparison']) 
    		  		&& $params['comparison'] 
    		  		&& isset($comparison_array[$params['comparison']]) 
    		  		&& $comparison_array[$params['comparison']] 
    		  		&& isset($params['column'])
    		  		&& array_key_exists($params['column'], $sortable_fields)) // $sortable_fields -- insuring against SQL injection
    			{
    		
      				switch($params['comparison']){
        				case "contains": //contains
    						$comparison_and_value = " LIKE ? ";
    						$pdo_params[] = "%".$params['value']."%";
    					break;
    					case "not_contains": //does not contain
    						$comparison_and_value = " NOT LIKE ? ";
    						$pdo_params[] = "%".$params['value']."%";
    					break;
    					case "start_with": //starts with
    						$comparison_and_value = " LIKE ? ";
    						$pdo_params[] = $params['value']."%";
    					break;
    					case "end_with": //ends with
    						$comparison_and_value = " LIKE ? ";
    						$pdo_params[] = "%".$params['value'];
    					break;
    					default:
    				  		$comparison_and_value = $comparison_array[$params['comparison']]." ? ";
    				  		$pdo_params[] = $params['value'];
      				}
					
      				$column_filter_array[] = " ({$params['column']} {$comparison_and_value}) ";
    			}
  			}
  		
  			if($column_filter_array){
    			$column_filter_sql = " AND (".implode(' AND ',$column_filter_array).") ";
  			}
		}
		
		$statement = $this->db->prepare("
    		SELECT SQL_CALC_FOUND_ROWS
    			   support_category.support_category_id AS manage
    			   ,support_category.category_name AS name
    			   ,support_category.support_category_id AS DT_RowId
    		FROM core_framework.support_category
    		LEFT JOIN support_site ON support_site.support_site_id = support_category.site_id
    		WHERE support_site.site_name = ?
    		{$search_sql}
    		GROUP BY support_category.support_category_id
			HAVING 1=1
			{$column_filter_sql}
      		{$sort}
			{$limit_sql}");
		$statement->execute($pdo_params);
		$data["aaData"] = $statement->fetchAll(PDO::FETCH_ASSOC);
	  	$statement = $this->db->prepare("SELECT FOUND_ROWS()");
  		$statement->execute();
  		$count = $statement->fetch(PDO::FETCH_ASSOC);
		$data["iTotalRecords"] = $count["FOUND_ROWS()"];
		$data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];
		return $data;
	}
	
	public function get_configuration(){
		$statement = $this->db->prepare("
    		SELECT *
    		FROM support_configuration
    		LEFT JOIN support_site ON support_site.support_site_id = support_configuration.support_site_id
    		WHERE support_site.site_name = :site_name");
		$statement->bindValue(":site_name", $this->site_key, PDO::PARAM_STR);
		$statement->execute();
		$result = $statement->fetch(PDO::FETCH_ASSOC);
		return $result;
	}
	
	public function update_configuration($post){
		$statement = $this->db->prepare("
	    	DELETE FROM support_configuration
	    	WHERE support_site_id = (SELECT support_site_id FROM support_site WHERE site_name = :site_name)");
		$statement->bindValue(":site_name", $this->site_key, PDO::PARAM_STR);
		$statement->execute();
		
		$statement = $this->db->prepare("
	    	INSERT INTO support_configuration
	    		(support_site_id
	    		,admin_emails
	    		,show_file_upload
	    		,confirmation_email
				,email_from
				,email_subject
				,email_body
				,email_signature
				,last_modified)
	    	VALUES
	    		((SELECT support_site_id FROM support_site WHERE site_name = :site_name)
	    		,:admin_emails
	    		,:show_file_upload
	    		,:confirmation_email
				,:email_from
				,:email_subject
				,:email_body
				,:email_signature
				,NOW())");
		$statement->bindValue(":site_name", $this->site_key, PDO::PARAM_STR);
		$statement->bindValue(":admin_emails", (isset($post['admin_emails']) && $post['admin_emails']) ? $post['admin_emails'] : null, PDO::PARAM_STR);
		$statement->bindValue(":show_file_upload", (isset($post['show_file_upload']) && $post['show_file_upload']) ? 1 : 0, PDO::PARAM_INT);
		$statement->bindValue(":confirmation_email", (isset($post['confirmation_email']) && $post['confirmation_email']) ? 1 : 0, PDO::PARAM_INT);
		$statement->bindValue(":email_from", (isset($post['email_from']) && $post['email_from']) ? $post['email_from'] : null, PDO::PARAM_STR);
		$statement->bindValue(":email_subject", (isset($post['email_subject']) && $post['email_subject']) ? $post['email_subject'] : null, PDO::PARAM_STR);
		$statement->bindValue(":email_body", (isset($post['email_body']) && $post['email_body']) ? $post['email_body'] : null, PDO::PARAM_STR);
		$statement->bindValue(":email_signature", (isset($post['email_signature']) && $post['email_signature']) ? $post['email_signature'] : null, PDO::PARAM_STR);
		$statement->execute();
		return $post;
	}

	public function manage_categories($post,$category_id=false){
		if($category_id){
			$statement = $this->db->prepare("
		    	UPDATE support_category
		    	SET category_name = :category_name
		    		,last_modified = NOW()
		    	WHERE support_category_id = :support_category_id");
			$statement->bindValue(":category_name", $post['category_name'], PDO::PARAM_STR);
			$statement->bindValue(":support_category_id", $category_id, PDO::PARAM_INT);
			$statement->execute();
		}else{
			$statement = $this->db->prepare("
		    	INSERT INTO support_category
		    		(category_name
		    		,site_id
					,created_date
					,last_modified)
		    	VALUES
		    		(:category_name
		    		,(SELECT support_site_id FROM support_site WHERE site_name = :site_name)
					,NOW()
					,NOW())");
			$statement->bindValue(":category_name", $post['category_name'], PDO::PARAM_STR);
			$statement->bindValue(":site_name", $this->site_key, PDO::PARAM_STR);
			$statement->execute();
		}
		return $post;
	}

	public function delete_category($category_id){
		$statement = $this->db->prepare("
	    	DELETE FROM support_category
	    	WHERE site_id = (SELECT support_site_id FROM support_site WHERE site_name = :site_name)
	    	AND support_category_id = :support_category_id");
		$statement->bindValue(":site_name", $this->site_key, PDO::PARAM_STR);
		$statement->bindValue(":support_category_id", $category_id, PDO::PARAM_INT);
		$statement->execute();
	}
	
	public function delete_support($support_id){
		$statement = $this->db->prepare("
	    	UPDATE support_request
	    	SET active = 0
	    	WHERE support_site_id = (SELECT support_site_id FROM support_site WHERE site_name = :site_name)
	    	AND support_request_id = :support_request_id");
		$statement->bindValue(":site_name", $this->site_key, PDO::PARAM_STR);
		$statement->bindValue(":support_request_id", $support_id, PDO::PARAM_INT);
		$statement->execute();
	}
	
	public function get_support_request($support_id){
		$statement = $this->db->prepare("
    		SELECT support_request.support_request_id
    			  ,support_request.first_name
    			  ,support_request.last_name
    			  ,support_request.email
    			  ,support_request.title
    			  ,support_request.body
    			  ,support_request.created_date
    			  ,support_category.category_name
    		FROM support_request
    		LEFT JOIN support_category ON support_category.support_category_id = support_request.support_category_id
    		WHERE support_site_id = (SELECT support_site_id FROM support_site WHERE site_name = :site_name)
    		AND support_request_id = :support_request_id");
		$statement->bindValue(":site_name", $this->site_key, PDO::PARAM_STR);
		$statement->bindValue(":support_request_id", $support_id, PDO::PARAM_INT);
		$statement->execute();
		$result = $statement->fetch(PDO::FETCH_ASSOC);
		return $result;
	}
	
	public function get_support_file_info($support_id){
		$statement = $this->db->prepare("
    		SELECT support_file_id
    			  ,file_name
    			  ,file_type
    			  ,file_size
    		FROM support_file
    		WHERE support_request_id = :support_request_id");
		$statement->bindValue(":support_request_id", $support_id, PDO::PARAM_INT);
		$statement->execute();
		$result = $statement->fetch(PDO::FETCH_ASSOC);
		return $result;
	}
	
	public function get_support_file_contents($file_id){
		$statement = $this->db->prepare("
    		SELECT file_content
    			   ,file_type
    			   ,file_name
    		FROM support_file
    		WHERE support_file_id = :support_file_id");
		$statement->bindValue(":support_file_id", $file_id, PDO::PARAM_INT); 
		$statement->execute();
		$result = $statement->fetch(PDO::FETCH_ASSOC);
		return $result;
	}
}
?>