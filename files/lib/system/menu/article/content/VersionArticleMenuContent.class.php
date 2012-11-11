<?php
namespace wiki\system\menu\article\content;
use wiki\data\article\ArticleCache;

use wcf\system\cache\CacheHandler;
use wcf\system\event\EventHandler;
use wcf\system\menu\article\content\IArticleMenuContent;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * @author	Rene Gessinger (NurPech)
 * @copyright	2012 woltnet
 * @package	com.woltnet.wiki
 * @subpackage	system.menu.article.content
 * @category 	WoltNet - Wiki
 */
class VersionArticleMenuContent extends SingletonFactory implements IArticleMenuContent {
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		EventHandler::getInstance()->fireAction($this, 'shouldInit');

		EventHandler::getInstance()->fireAction($this, 'didInit');
	}

	/**
	 * @see	wiki\system\menu\article\content\IArticleMenuContent::getContent()
	 */
	public function getContent($articleID) {
		$article = ArticleCache::getInstance()->getArticle($articleID);
		WCF::getTPL()->assign(array(
			'article'	=> $article
		));
		return WCF::getTPL()->fetch('articleVersionList', 'wiki');
	}
}