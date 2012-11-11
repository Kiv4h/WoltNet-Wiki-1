<article class="message dividers marginTop" style="display: inline-block;">
	<div>
		<section class="messageContent">
			<div>
				<div class="messageBody">
					<div class="messageText" style="border: 0;">
						{@$article->getFormattedMessage()}
					</div>

					<footer class="messageOptions contentOptions marginTop clearfix">
						<nav>
							<ul class="smallButtons">
								{if $article->userID != $__wcf->getUser()->userID}<li class="jsReportArticle" data-object-id="{@$article->articleID}"><a title="{lang}wiki.global.button.report{/lang}" class="button jsTooltip"><img src="{icon}warning{/icon}" alt="" class="icon16" /></a></li>{/if}
								{if !$article->isActive && $article->getModeratorPermission('canActivateArticle')}<li><a href="{link application='wiki' controller='ArticleActivate' object=$article}{/link}" class="button"><img src="{@$__wcf->getPath()}icon/check.svg" alt="" title="{lang}wiki.global.button.activate{/lang}" /> <span>{lang}wiki.global.button.activate{/lang}</span></a></li>{/if}
								{if $article->isEditable()}<li><a href="{link application='wiki' controller='ArticleEdit' object=$article}{/link}" class="button"><img src="{@$__wcf->getPath()}icon/edit.svg" alt="" title="{lang}wiki.global.button.edit{/lang}" /> <span>{lang}wiki.global.button.edit{/lang}</span></a></li>{/if}
								{if $article->isTrashable()}<li><a href="{link application='wiki' controller='ArticleTrash' object=$article}{/link}" class="button"><img src="{@$__wcf->getPath()}icon/trash.svg" alt="" title="{lang}wiki.global.button.trash{/lang}" /> <span>{lang}wiki.global.button.trash{/lang}</span></a></li>{/if}
							</ul>
						</nav>
					</footer>
				</div>
			</div>
		</section>
	</div>
</article>