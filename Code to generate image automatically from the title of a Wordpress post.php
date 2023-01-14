<?php



/*add_filter('post_thumbnail_html', 'set_featured_image_from_attachment');*/
/*add_filter('post_thumbnail_url', 'set_featured_image_from_attachment');*/
/*add_filter('admin_post_thumbnail_html', 'set_featured_image_from_attachment');*/
/*add_action('save_post', 'set_first_as_featured');*/
/*add_action( 'publish_post', 'set_featured_image_from_attachment', 10, 2 );*/
/*add_filter('the_content', 'set_featured_image_from_attachment');*/
add_filter('wp_insert_post', 'set_featured_image_from_attachment', 99999 );
function set_featured_image_from_attachment($content) {
	
	global $post;
	if (has_post_thumbnail()) {
		// display the featured image
		the_post_thumbnail();
	} else {

		// it allows us to use download_url() and wp_handle_sideload() functions
	require_once( ABSPATH . 'wp-admin/includes/file.php' );

	$args = array(
		'post_type'      => 'attachment',
		'numberposts'    => -1, // show all
		'post_status'    => 'any',
		'post_mime_type' => 'image',
		'orderby'        => 'menu_order',
		'order'           => 'ASC'
	);

	$images = get_posts($args);
					
	foreach($images as $kg => $image) {

		/************* Récuperer nom du slug d'un article, le diviser puis l'insérer dans un lien et le comparer aux liens d'images ****************/
				
		$auto_ipt = get_the_title(); 
		$regex = array('/[^\p{L}\p{N}\s]/u', '/\s/');
    	$repl  = array('', '-');
    	$slug = preg_replace($regex, $repl, $auto_ipt);
						
		$names = explode('-', $slug);
		}
						
		/************ Recupérer le nom don la longueur est la plus importante *********/
		usort($names, function ($a, $b) {
			return strlen($a) < strlen($b);
		});

		$newK = reset($names);
		/*
		echo '<pre>';
		var_dump(reset($names)); // Facebook
		echo '</pre>';*/

		/**** On affiche les deux clés qui nous intéressent du tableau reprenant la "piece d'identité" de chaque article *******/
		$newId = array_column($images, 'ID');
		$newImg = array_column($images, 'guid');

		/********* On fait en sorte que l'ID devienne la clé de chaque URL ************/
		$fusion=array_combine($newId,$newImg);


		$mystring = $newK;


		/************************* Begin of new code for external source ****************************/

		$nodes = array(
			'https://ivredevi.fr/wp-json/wp/v2/media?per_page=100&page=1',
		);
		
		$node_count = count($nodes);
		
		$curl_arr = array();
		$master = curl_multi_init();
		
		for($i = 0; $i < $node_count; $i++)
		{
			$url =$nodes[$i];
			$curl_arr[$i] = curl_init($url);
			curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
			curl_multi_add_handle($master, $curl_arr[$i]);
		}
		
		do {
			curl_multi_exec($master,$running);
		} while($running > 0);
		
		
		for($i = 0; $i < $node_count; $i++)
		{
			$results = curl_multi_getcontent  ( $curl_arr[$i]  );
			$img = json_decode($results, true);
		
			/*$pst = array_column($img, 'name');*/
		
			$mls = array_map(function ($ar) {return $ar['source_url'];}, $img);
		
			$arr = "/$mystring/i";
		
			$res = preg_grep($arr, $mls);
		
			$cres = count($res);
		


			/**************************End of new code for external source ******************************/

				if($cres > 1){
					$ak=array_rand($res,1);
					$content = $res[$ak[0]];
				}else{
					$content = $res;
				}

			/************* End foreach  **************/
			$post_id = get_the_id(); 

			if(!empty($content)){
				foreach ($content as $key => $vlg) {
					
					$upload_dir = wp_upload_dir();
					$image_data = file_get_contents($vlg);
					$filename = basename($vlg);
					if(wp_mkdir_p($upload_dir['path']))
						$file = $upload_dir['path'] . '/' . $filename;
					else
						$file = $upload_dir['basedir'] . '/' . $filename;
					file_put_contents($file, $image_data);
		
					$wp_filetype = wp_check_filetype($filename, null );
					$attachment = array(
						'post_mime_type' => $wp_filetype['type'],
						'post_title' => sanitize_file_name($filename),
						'post_content' => '',
						'post_status' => 'inherit'
					);
					$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
					require_once(ABSPATH . 'wp-admin/includes/image.php');
					$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
					wp_update_attachment_metadata( $attach_id, $attach_data );
		
					$content = set_post_thumbnail( $post_id, $attach_id );
				}

			}else{
				
				$nds = array(
					'https://ivredevi.fr/wp-content/themes/ivredevi/images/news/pressedujour-buzz-et-actualites.jpg',
					'https://ivredevi.fr/wp-content/themes/ivredevi/images/news/pressedujour-en-france-et-à-l-internationale.jpg',
					'https://ivredevi.fr/wp-content/themes/ivredevi/images/news/pressedujour-infos-internationales.jpg',
					'https://ivredevi.fr/wp-content/themes/ivredevi/images/news/pressedujour-l-info-jour-pour-jour.jpg',
					'https://ivredevi.fr/wp-content/themes/ivredevi/images/news/pressedujour-l-info-sur-la-cryptomonnaie.jpg',
					'https://ivredevi.fr/wp-content/themes/ivredevi/images/news/pressedujour-premier-sur-l-info-dans-le-monde.jpg',
				);

				$rand_k=array_rand($nds,2);

				$newimg_url = $nds[$rand_k[0]];

				$upload_dir = wp_upload_dir();
				$image_data = file_get_contents($newimg_url);
				$filename = basename($newimg_url);

				if(wp_mkdir_p($upload_dir['path']))
						$file = $upload_dir['path'] . '/' . $filename;
					else
						$file = $upload_dir['basedir'] . '/' . $filename;
					file_put_contents($file, $image_data);
				$wp_filetype = wp_check_filetype($filename, null );
				$img_post_title = sanitize_file_name($filename);
				$attachment = array(
					'guid'           => $newimg_url, 
					'post_mime_type' => $wp_filetype['type'],
					'post_title' => sanitize_file_name($filename),
					'post_content'   => '',
					'post_status'    => 'inherit'
					);
					$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
					require_once( ABSPATH . 'wp-admin/includes/image.php' );
					$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
					wp_update_attachment_metadata( $attach_id, $attach_data );
					update_post_meta( $attach_id, '_wp_attachment_image_alt', wp_slash($img_post_title) );
				
					// Set the image as the featured image
					set_post_thumbnail( $post_id, $attach_id );
			}
			
			
		}
		return $content;		
	}	

}


