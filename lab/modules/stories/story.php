<!DOCTYPE html>
<html amp>
	<head>
		<meta charset="utf-8">
		<title><?=$title?></title>

		<link rel="canonical" href="<?=$canonical?>">

		<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
			
		<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
		
		<script async custom-element="amp-video" src="https://cdn.ampproject.org/v0/amp-video-0.1.js"></script>
		<script async custom-element="amp-story" src="https://cdn.ampproject.org/v0/amp-story-1.0.js"></script>
		<script async src="https://cdn.ampproject.org/v0.js"></script>
		<style amp-custom>
		<?=$fonts?>
		amp-story-grid-layer>section{
			height:90%;
		}
		amp-story-grid-layer>section>div{
			height:100%;
			display:flex;
			flex-direction:column;
			font-family:"open sans";
		}
		.font-14px{ font-size:14px; }
		.font-15px{ font-size:15px; }
		.font-16px{ font-size:16px; }
		.font-18px{ font-size:18px; }
		.font-20px{ font-size:20px; }
		.font-22px{ font-size:22px; }
		.font-24px{ font-size:24px; }
		.font-26px{ font-size:26px; }
		.font-28px{ font-size:28px; }
		.font-30px{ font-size:30px; }
		.font-32px{ font-size:32px; }
		.font-36px{ font-size:36px; }
		.font-42px{ font-size:42px; }
		
		.black{ color:black; }
		.white{ color:white; }
		.orange{ color:orange; }
		.crimson{ color:crimson; }
		.cornsilk{ color:cornsilk; }
		.steelblue{ color:steelblue; }
		.mediumaquamarine{ color:mediumaquamarine; }
		
		p.brown-bg{ background-color:brown; }
		p.silver-bg{ background-color:silver; }
		p.crimson-bg{ background-color:crimson; }
		p.seagreen-bg{ background-color:seagreen; }
		p.steelblue-bg{ background-color:steelblue; }
		p.alphablack-bg{ background-color:rgba(0,0,0, .7); }
		p.alphawhite-bg{ background-color:rgba(255,255,255, .7); }
		p.transparen-bg{ background-color:transparent; }

		span.gold-bg{ background-color:gold; }
		span.black-bg{ background-color:black; }
		span.white-bg{ background-color:white; }
		span.orange-bg{ background-color:orange; }
		span.purple-bg{ background-color:purple; }
		span.peachpuff-bg{ background-color:peachpuff; }
		span.rosybrown-bg{ background-color:rosybrown; }
		span.transparen-bg{ background-color:transparent; }

		div.center{ justify-content:center; }
		div.flex-end{ justify-content:flex-end; }
		div.flex-start{ justify-content:flex-start; }
		div.space-around{ justify-content:space-around; }
		div.space-between{ justify-content:space-between; }
		
		a.copyright{
			left:0;
			bottom:1px;
			width:100%;
			color:#555;
			display:block;
			font-size:12px;
			position:absolute;
			text-align:center;
			background-color:rgba(255,255,255, .5);
		}
		p{
			padding:5px;
			font-size:none;
		}
		p.left{ text-align:left; }
		p.right{ text-align:right; }
		p.center{ text-align:center; }
		img{
			object-position:center center;
			-webkit-object-position:center center;
		}
		p>span{
			padding:0 4px;
			font-size:18px;
			font-weight:bold;
			white-space:pre-line;
		}
		</style>
	</head>
	<body>
		<amp-story standalone title="<?=$page['header']?>" publisher="Lifter" publisher-logo-src="https://<?=$_SERVER['HTTP_HOST']?>/themes/2018/images/lifter.png" poster-portrait-src="<?=$page['portrait']?>" poster-landscape-src="<?=$page['preview']?>">
			<amp-story-page id="cover">
			<amp-story-grid-layer template="fill">
				<amp-img src="/images/cover.jpg" width="<?=$card['width']?>" height="<?=$card['height']?>" layout="responsive"></amp-img>
			</amp-story-grid-layer>
			<amp-story-grid-layer template="fill">
				<section>
					<div class="<?=($card['justify'].' '.$field['background'])?>">
					<?foreach($card['fields'] as $field):?>
						<p animate-in="<?=$field['animate']?>" class="<?=($field['align'].' '.$field['background'])?>">
						<?foreach($field['words'] as $word):?>
							<span class="font-<?=($word['font'].' '.$word['color'].' '.$word['background'])?>"><?=$word['content']?></span>
						<?endforeach?>
						</p>
					<?endforeach?>
					</div>
				</section>
			</amp-story-grid-layer>
			</amp-story-page>
			<amp-script src=”/modules/stories/tpl/story.js” layout=responsive>
  
			</amp-script>
			<script custom-element="c-bble">
				document.getContent = function(){
					return document.querySelector("amp-story").outerHTML;
				}
				document.changeImage = function(value){
					let img = document.querySelector("amp-story>amp-story-page>amp-story-grid-layer>amp-img");
					console.log(img);
				}
			</script>
		</amp-story>
	</body>
</html>