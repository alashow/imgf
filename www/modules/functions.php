<?php

function getImages($page){
	global $config;

	$images = array();

	$client = new Tumblr\API\Client();

	$reversed = isset($_REQUEST['reversed']);
	$count = $_REQUEST['count'];

	if (isset($count)) {
		$count = intval($count);
		if ($count > 0) {
			$count = ($count > 50) ? 50 : $count;
		} else {
			$count = $config['default_count'];
		}
	} else {
		$count = $config['default_count'];
	}

	$options['type'] = "photo";
	$options['filter'] = "text";
	$options['limit'] = $count;
	
	if ($reversed) {
		try {	
			$total_posts = $client->getBlogPosts($config['blog'] . ".tumblr.com", $options)->total_posts;
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
		$response['error_message'] = "Eww, there's no so much images!";
	} else {
		$response['title'] = $blog->title;
		$response['total_count'] = $data->total_posts;
		$response['requested_count'] = $count;
		$response['images'] = $images;
	}

	return $response;
}

function trimTumblrException($exception){
	//trim some strings for normal exception message
	$exception = str_replace("Tumblr\API\Request", "", $exception);
	$exception = str_replace("[", "", $exception);
	$exception = str_replace("]:", "", $exception);

	return $exception;
}

function getAsJson(){
	$images = getImages(intval($_GET['page']));
	die(json_encode($images));
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
 * @param $totalRecords
 * @return string
 */
function createLinks($totalRecords, $currentPage, $perPage, $maxPages = 4){
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
            $currentClass = $page == $currentPage ? 'active current' : '';
            $output .= '<li class="' . $currentClass . '"><a href="' . buildQueryString($page) . '">' . $page . '</a></li>';
        }
        $output .= '<li class="' . $nextLiClass . '"><a href="' . $nextLinkHref . '">&raquo;</a></li>';
        $output .= '</ul>';
        return $output;
}

function buildQueryString($page){
        $get = $_GET;
        $get['page'] = $page;
        $queryString = http_build_query($get);
        return $queryString = '?' . $queryString;
}

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