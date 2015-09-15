<?php
	require '../vendor/autoload.php';
	require 'config.php';
	require 'modules/functions.php';

	$blog = $_REQUEST['blog'];

	if (isset($blog)) {
		$config['blog'] = $blog;
	}

	if (isset($_REQUEST['asJson'])) {
		getAsJson();
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<link rel="icon" type="image/png" href="css/icon.png">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
		<title><?=$config['page_title']?></title>
		<link rel="stylesheet" href="/css/materialize.min.css">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link rel="stylesheet" href="/css/style.min.css?v=1.0.1">
		<link rel="stylesheet" href="http://photoswipe.com/dist/photoswipe.css?v=4.1.0-1.0.4">
		<link rel="stylesheet" href="http://photoswipe.com/dist/default-skin/default-skin.css?v=4.1.0-1.0.4">
	</head>
	<body>
		<div id="wrapper">
			<!-- Navbar -->
			<nav class="grey darken-2" role="navigation">
				<div class="nav-wrapper container">
					<div class="nav-wrapper">
						<a href="/" class="brand-logo"><?=$config['page_title']?></a>
					</div>
				</div>
			</nav>
			<div class="container body">
				<div class="row">
					<div class="col s12">
					<?
						$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

						$data = getImages($page - 1);

						$total = $data['total_count'];
						$perPage = $data['requested_count'];

						$lastPage = ceil($total / $perPage);
						
						$lastPageLink = buildQueryString($lastPage);
						$reversedLink = getReversedQueryString();
						$blogLink = "http://". $config['blog'] . ".tumblr.com";

						$images = $data['images'];

						if (!empty($images)) {?>
							<div class="row">
								<div class="col s12">
									<a href="<?=$$reversedLink?>" class="waves-effect waves-light btn grey darken-2 tooltipped" data-position="bottom" data-delay="50" data-tooltip="Reversed pagination">Reversed</a>
									<a href="<?=$lastPageLink?>" class="waves-effect waves-light btn grey darken-2 tooltipped" data-position="bottom" data-delay="50" data-tooltip="Navigate to last page">Last Page</a>
									<a id="anotherBlog" class="waves-effect waves-light btn grey darken-2 tooltipped" data-position="bottom" data-delay="50" data-tooltip="Switch to another blog">Another Blog</a>
								</div>
							</div>
							<h5><a href="<?=$blogLink?>" target="_blank" class="grey-text text-darken-2 tooltipped" data-position="top" data-delay="50" data-tooltip="Open blog owner page"><?=$data['title']?></a></h5>	
							<div class="gallery">
								<?foreach ($images as $image) {?>
									<a href="<?=$image['src']?>" data-size="<?=$image['width']?>x<?=$image['height']?>">
										<img src="<?=$image['src']?>" />
										<figure><?=$image['caption']?></figure>
									</a>
								<?}
								?>
							</div>
						<?if($lastPage > 1){?>
							<div class="col s8">
								<?=createLinks($total, $page, $perPage)?>
							</div>
						<?}
						} else {
							errorView($data['error_message']);
						}?>
					</div>
				</div>
				<div id="footer" class="section">
					© 2015 <a class="grey-text text-darken-2" href="http://alashov.com" target="_blank">by Alashov</a>
				</div>
			</div>
		</div>
		<!-- PhotoSwipe Markup -->
		<div id="gallery" class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="pswp__bg"></div>
			<div class="pswp__scroll-wrap">
				<div class="pswp__container">
					<div class="pswp__item"></div>
					<div class="pswp__item"></div>
					<div class="pswp__item"></div>
				</div>
				<div class="pswp__ui pswp__ui--hidden">
					<div class="pswp__top-bar">
						<div class="pswp__counter"></div>
						<button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
						<button class="pswp__button pswp__button--share" title="Share"></button>
						<button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
						<button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
						<div class="pswp__preloader">
							<div class="pswp__preloader__icn">
								<div class="pswp__preloader__cut">
									<div class="pswp__preloader__donut"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
						<div class="pswp__share-tooltip">
						</div>
					</div>
					<button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>
					<button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>
					<div class="pswp__caption">
						<div class="pswp__caption__center">
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
		<script>if (!window.jQuery) { document.write('<script src="/js/jquery.min.js"><\/script>');}</script>
		<script src="/js/materialize.min.js"></script>
		<script src="http://photoswipe.com/dist/photoswipe.min.js?v=4.1.0-1.0.4"></script>
		<script src="http://photoswipe.com/dist/photoswipe-ui-default.min.js?v=4.1.0-1.0.4"></script>
		<script src="/js/app.min.js"></script>
	</body>
</html>