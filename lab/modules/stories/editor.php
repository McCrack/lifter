<!DOCTYPE html>
<html>
	<head id="head">
		<meta http-equiv="Cache-Control" content="no-cache"/>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<style>
		<?php
		$fontset = "";
		foreach(scandir("../".BASE_FOLDER."/fonts") as $file):
			$font = explode(".", $file);
			if(is_file("../".BASE_FOLDER."/fonts/".$file) && $font[1]==="ttf"):
				$fontset .= "<option value='".$font[0]."'>".$font[0]."</option>"?>
				@font-face{
					font-family:"<?=$font[0]?>";
					src: local("<?=$font[0]?>"), url(/editor/fonts-aggregator/"<?=$font[0]?>");
				}
			<?endif;
		endforeach?>
		</style>
		<style>
			@font-face {
  				font-family:'iconset';
  				src: url(/modules/editor/tpl/fonts/editor.ttf) format('truetype');
			}
			html,body{
				margin:0px;
				height:100%;
				overflow:hidden;
				background-image:url(/built-in/materials/images/shattered.png);
			}

			#content:focus{ outline:none; }
			#toolbar{
				z-index:2;
				top:0;
				left:0;
				width:100%;
				height:39px;
				position:fixed;
			
				display:flex;
				align-items:center;
				justify-content:center;
			}
			.tool,
			#toolbar{
				background-color:#EEE;
				background:linear-gradient(to top, #DDD, #FFF);
			}
			.tool{
				color:#578;
				width:38px;
				height:35px;
				border-radius:3px;
				text-align:center;
				vertical-align:top;
				display:inline-block;
				font:16px/38px iconset,calibri,helvetica;
			}
			.toolset{
				width:inherit;
				height:inherit;
				overflow:hidden;
				position:absolute;
			}
			label.tool{ margin:1px 0; }
			input.tool{
				margin:2px;
				width:64px;
				height:33px;
				padding:5px;
				background:white;
				border:1px solid #BBB;
				box-sizing:border-box;
			}
			label.tool:hover{
				color:#FFF;
				background:#E55;
			}
			.toolset:hover{
				height:auto;
				padding:0px 4px 4px 0px;
			}
			.toolset:hover>.tool{
				box-shadow:5px 5px 3px -4px rgba(0,0,0, .5);
			}
			.right{ float:right; }
			
			/*********************/

			.select{
    			z-index:1;
    			color:#555;
    			position:relative;
    			border-radius:3px;
    			display:inline-block;
    			border:1px solid #BBB;
    			background-color:#F8F8F8;
			}
			.select>select{
    			height:26px;
    			border-width:0;
    			padding-right:20px;
    			-moz-appearance:none;
    			-webkit-appearance:none;
    			background-color:transparent;
			}
			.select::after{
    			top:5px;
    			right:5px;
    			z-index:-1;
    			content:"❯";
    			font-size:12px;
    			position:absolute;
			}
			/**********************************/
			#content{
				font-size:0;
				margin-top:36px;
				height:calc(100% - 36px);
				background-color:transparent;

				overflow:auto;
				white-space:nowrap;
			}
			#content>div.card{
				width:100%;
				height:100%;
				vertical-align:top;
				display:inline-block;
				outline:1px solid #AAA;

				position:relative;
			}
			.layer{
				top:0;
				left:0;
				width:100%;
				height:100%;
				position:absolute;
			}
			.layer>img,
			.layer>video{
				width:100%;
				height:100%;
				object-fit:cover;
			}
			figure{
				margin:0;
				padding:0;
			}
			section.layer{
				font-size:16px;
				display:flex;
				flex-direction:column;
			}
			section.center{ justify-content:center; }
			section.flex-start{ justify-content:flex-start; }
			section.flex-end{ justify-content:flex-end; }
			section.space-around{ justify-content:space-around; }
			section.space-between{ justify-content:space-between; }

			section.layer>div{
				color:white;
				padding:5px;
				min-height:20px;
				outline:1px dotted white;
			}
		</style>
		<script defer src="/js/md5.js"></script>
		<script defer src="/js/gbAPI.js"></script>
		<script defer src="/modules/stories/tpl/editor.js"></script>
		<script>
			document.getContent = function(){
				return document.querySelector("#content").innerHTML;
			}
			document.setFlex = function(value){
				//var story = document.querySelector("#content");
				//var offset = (story.scrollLeft/story.offsetWidth>>0);
			}
		</script>
	</head>
	<body>
		<form id="toolbar" onsubmit="return false">
			<label data-translate="title" title="spell check" class="tool" onclick="doc.spellCheck()">&#xea12;</label>
			<div class="tool">
				<div class="toolset">
					<label data-translate="title" title="create link" class="tool" data-tag="A" onmousedown="doc.createlink();">&#xe9cb;</label>
					<label data-translate="title" title="bold" class="tool" data-tag="B" onmousedown="doc.insertTag('bold', 'B')">&#xea62;</label>
					<label data-translate="title" title="italic" class="tool" data-tag="I" onmousedown="doc.insertTag('italic','I')">&#xea64;</label>
					<label data-translate="title" title="underline" class="tool" data-tag="U" onmousedown="doc.insertTag('underline','U')">&#xea63;</label>
					<label data-translate="title" title="strike" class="tool" data-tag="S" onmousedown="doc.insertTag('strikeThrough','S')">&#xea65;</label>
				</div>
			</div>
			<div class="tool">
				<div class="toolset">
					<label data-translate="title" title="insert image" class="tool" data-tag="IMG" onmousedown="doc.imgBox()">&#xe90d;</label>
					<label data-translate="title" title="insert album" class="tool" data-tag="FIGURE" onmousedown="doc.albumBox()">&#xe90e;</label>
				</div>
			</div>
			<div class="tool">
				<div class="toolset">					
					<label data-translate="title" title="bulleted list" class="tool" data-tag="UL" onmousedown="doc.list('insertUnorderedList')">&#xe9bb;</label>
					<label data-translate="title" title="numbered list" class="tool" data-tag="OL" onmousedown="doc.list('insertOrderedList')">&#xe9b9;</label>
				</div>
			</div>
			<div class="tool">
				<div class="toolset">
					<label data-translate="title" title="align left" class="tool" onmousedown="doc.setProperty('align','left')">&#xea77;</label>
					<label data-translate="title" title="align center" class="tool" onmousedown="doc.setProperty('align','center')">&#xea78;</label>
					<label data-translate="title" title="align justify" class="tool" onmousedown="doc.setProperty('align','justify')">&#xea7a;</label>
					<label data-translate="title" title="align right" class="tool" onmousedown="doc.setProperty('align', 'right')">&#xea79;</label>
				</div>
			</div>
			<div class="select">
				<select name="fsize" data-translate="title" title="font size" oninput="doc.setFontSize(this.value)">
					<option>12px</option>
					<option>14px</option>
					<option>16px</option>
					<option>18px</option>
					<option>22px</option>
					<option>24px</option>
					<option>28px</option>
					<option>32px</option>
					<option>36px</option>
					<option>48px</option>
					<option>52px</option>
					<option>60px</option>
				</select>
			</div>
		</form>
			<main id="content">
				<div class="card">
					<figure class="layer">
						<img src="/images/cover.jpg">
					</figure>
					<section class="layer space-between" contenteditable="true">
						<div></div>
						<div></div>
						<div></div>
					</section>
				</div>
				<div class="card">
					<figure class="layer">
						<video autoplay loop src="https://img.lifter.com.ua/data/2018/august/2952/storygymnastics01.mov" type="video/quicktime"></video>
					</figure>
					<section class="layer space-around" contenteditable="true">
						<div></div>
						<div></div>
						<div></div>
					</section>
				</div>
				<div class="card">
					<figure class="layer">
						<video autoplay loop src="https://img.lifter.com.ua/data/2018/august/2952/storygymnastics02.mov" type="video/quicktime"></video>
					</figure>
					<section class="layer flex-end" contenteditable="true">
						<div></div>
						<div></div>
					</section>
				</div>
			</main>
		<script>
		(function(body){
			
		})(document.currentScript.parentNode)
		</script>
	</body>
</html>