<?php

/**
 * @param $page pagination
 * @return array of data. i have not time to explain, sorry. see my hard-code, thanks.
 */
function getImages($page){
	global $config;

	$images = array();

	$client = new Tumblr\API\Client($config['tumblr_consumer_key']);

	$reversed = isset($_REQUEST['reversed']);

	$count = $config['post_limit'];

	$options['limit'] = $count;
	$options['type'] = "photo";
	$options['filter'] = "text";

	if ($reversed) {
		try {	
			$total_posts = getTotalPosts($client, $config['blog'], $options);
			$options['offset'] = $total_posts - (($page + 1) * $count); //yes, i didn't find other fixes..
		} catch(Tumblr\API\RequestException $e) {
			$response['error_message'] = trimTumblrException($e);
			return $response;
		}
	} else {
		$options['offset'] = $page * $count;
	}

	try {	
		$data = $client->getBlogPosts($config['blog'] . ".tumblr.com", $options);
	} catch(Tumblr\API\RequestException $e) {
		$response['error_message'] = trimTumblrException($e);
		return $response; //whe have nothing to do here now, bye.
	}
	
	$blog = $data->blog;
	$posts = $data->posts;
	
	foreach ($posts as $post) {
		$photos = $post->photos;
		if ($photos) {
			foreach ($photos as $photo) {
				$image = $photo->original_size;
				array_push($images, array("caption" => $post->caption, "src" => $image->url, "width" => $image->width, "height" => $image->height));
			}
		}
	}

	if (empty($images)) {
		if ($page > 1) {
			$response['error_message'] = "Eww, there's no so much images!";
		} else {
			$response['error_message'] = "There's no posts with images, sorry :(";
		}
	} else {
		$response['title'] = $blog->title;
		$response['blog_url'] = $blog->url;
		$response['total_count'] = $data->total_posts;
		$response['requested_count'] = $count;
		$response['images'] = $images;
	}

	return $response;
}

/**
 * @param $client ready to use tumblr api client
 * @param $blog blog domain
 * @param $options api request options
 * @return total posts of blog
 */
function getTotalPosts($client, $blog, $options){
	global $config;

	return $client->getBlogPosts($blog . ".tumblr.com", $options)->total_posts;
}

/**
 * Die and return json
 */
function getAsJson(){
	$images = getImages(intval($_GET['page']));
	die(json_encode($images));
}

/**
 * @param $exception message for trim
 * @return user friendly exception message, thank me later
 */
function trimTumblrException($exception){
	//trim some strings for normal exception message
	$exception = str_replace("Tumblr\API\Request", "", $exception);
	$exception = str_replace("[", "", $exception);
	$exception = str_replace("]:", "", $exception);

	return $exception;
}

/**
 * Get all photo posts of blog as json
 */
function getAll(){
	global $config;
	
	$client = new Tumblr\API\Client($config['tumblr_consumer_key']);

	$options['limit'] = 50;
	$options['type'] = "photo";
	$options['filter'] = "text";

	$config['post_limit'] = $options['limit'];

	$total = getTotalPosts($client, $config['blog'], $options);
	$response["images"] = array();

	for ($i=0; $i < ceil($total / $options['limit']); $i++) { 
		$data = getImages($i);

		$response['images'] = array_merge($response['images'], $data['images']);
	}


	$onlyUrl = isset($_REQUEST['onlyUrl']); 

	if ($onlyUrl) {
		foreach ($response['images'] as $image) {
			echo $image['src'] . "\r\n";
		}
		die;
	} else {
		die(json_encode($response));
	}
}

/**
 * @param $error Error Message
 * @param $size set custom size
 * @return markup of error message
 */
function errorView($error, $size = "s12") {
    echo ''?>
		<div class="row">
			<div class="col <?=$size?>">
				<div class="card-panel red">
					<span class="white-text">
						<?=$error?>
					</span>
				</div>
			</div>
		</div>
    <?php;
}

/**
 * @param $totalRecords how much of that shit you have?
 * @param $currentPage where is you now?
 * @param $perPage soo, how much do you want?
 * @param $maxPages now i reached limit of my imagination, derp
 * @return string
 */
function createLinks($totalRecords, $currentPage, $perPage, $maxPages = 4){
		global $config;

        $pages = getPages($totalRecords, $perPage, $currentPage, $maxPages);
        $prevLiClass = 'prev';
        $prevLinkHref = 'javascript:void(0)';

        if ($currentPage == 1) {
            $prevLiClass = 'disabled';
        } else {
            $prevLinkHref = buildQueryString($currentPage - 1);
        }
        $nextLiClass = 'next';
        $nextLinkHref = 'javascript:void(0)';
        if ($currentPage == $totalRecords) {
            $nextLiClass = 'disabled';
        } else {
            $nextLinkHref = buildQueryString($currentPage + 1);
        }
        $output = '<ul class="pagination">';
        $output .= '<li class="' . $prevLiClass . '"><a href="' . $prevLinkHref . '">&laquo;</a></li>';
        foreach($pages as $page) {
            $currentClass = $page == $currentPage ? "active current " . $config['theme'] : '';
            $output .= '<li class="' . $currentClass . '"><a href="' . buildQueryString($page) . '">' . $page . '</a></li>';
        }
        $output .= '<li class="' . $nextLiClass . '"><a href="' . $nextLinkHref . '">&raquo;</a></li>';
        $output .= '</ul>';
        return $output;
}

/**
 * @param $page page to create link with
 * @return url string with queries
 */
function buildQueryString($page){
        $get = $_GET;
        $get['page'] = $page;
        $queryString = http_build_query($get);
        return $queryString = '?' . $queryString;
}

/**
 * @return url string with queries and enabled reversed mode
 */
function getReversedQueryString(){
        $get = $_GET;
        $get['reversed'] = "true";
        $queryString = http_build_query($get);
        return $queryString = '?' . $queryString;
}

/**
 * @param $total
 * @param null $limit
 * @param null $current
 * @param null $adjacents
 * @return array
 *
 * Credit: http://stackoverflow.com/a/7562895/656489
 */
function getPages($total, $limit = null, $current = null, $adjacents = null) {
        $result = array();
        if (isset($total, $limit) === true)
        {
            $result = range(1, ceil($total / $limit));
            if (isset($current, $adjacents) === true)
            {
                if (($adjacents = floor($adjacents / 2) * 2 + 1) >= 1)
                {
                    $result = array_slice($result, max(0, min(count($result) - $adjacents, intval($current) - ceil($adjacents / 2))), $adjacents);
                }
            }
        }
        return $result;
}
?>