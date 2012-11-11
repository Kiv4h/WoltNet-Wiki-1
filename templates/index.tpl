{include file='documentHeader'}

<head>
	<title>{lang}wiki.index.title{/lang} - {PAGE_TITLE|language}</title>

	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}">

{capture assign='sidebar'}
	<nav id="sidebarContent" class="sidebarContent">
		{if $__boxSidebar|isset && $__boxSidebar}
			<ul>
				{@$__boxSidebar}
			</ul>
		{/if}
	</nav>
{/capture}

{include file='header' sidebarOrientation='right'}

<header class="boxHeadline">
	<hgroup>
		<h1>{PAGE_TITLE|language}</h1>
		{hascontent}<h2>{content}{PAGE_DESCRIPTION|language}{/content}</h2>{/hascontent}
	</hgroup>
</header>

{hascontent}
<div class="marginTop container containerPadding wikiAnnouncement">
	<fieldset>
		<legend>{lang}wiki.index.announcement{/lang}</legend>
			<p>{content}{@$wikiAnnouncement}{/content}</p>
	</fieldset>
</div>
{/hascontent}

<section id="dashboard">
	{if $__boxContent|isset}{@$__boxContent}{/if}
</section>

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link application='wiki' controller='ArticleAdd'}{/link}" title="{lang}wiki.global.button.articleAdd{/lang}" class="button"><img src="{icon size='M'}asterisk{/icon}" alt="" class="icon24" /> <span>{lang}wiki.global.button.articleAdd{/lang}</span></a></li>
			{event name='largeButtonsTop'}
		</ul>
	</nav>
</div>

{include file='categoryList'}

<div class="container marginTop shadow">
	<ul class="containerList">
		{if WIKI_INDEX_ENABLE_STATS}
			<li class="box24">
				<img src="{icon}chartVertical{/icon}" alt="" class="icon24" />
				<div>
					<hgroup class="containerHeadline">
						<h1>{lang}wiki.global.statistics{/lang}</h1>
						<h2>{lang}wiki.global.statistics.description{/lang}</h2>
					</hgroup>
				</div>
			</li>
		{/if}
	</ul>
</div>

{include file='footer'}

</body>
</html>