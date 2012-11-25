<?php
namespace wiki\data\article;
use wiki\util\ArticleUtil;
use wiki\data\article\label\ArticleLabel;

use wcf\system\search\SearchIndexManager;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\user\storage\UserStorageHandler;
use wcf\data\IClipboardAction;
use wcf\system\package\PackageDependencyHandler;
use wcf\system\exception\UserInputException;
use wcf\system\exception\ValidateActionException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\WCF;

/**
 * @author	Jean-Marc Licht
 * @copyright	2012 WoltNet
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltnet.wiki
 * @subpackage	data.article
 * @category 	WoltNet - Wiki
 */
class ArticleAction extends AbstractDatabaseObjectAction implements IClipboardAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wiki\data\article\ArticleEditor';

	/**
	 * list of active articles
	 * @var	array<wiki\data\article\Article>
	 */
	public $articles = array();

	/**
	 * @see DatabaseObjectEditor::create()
	 */
	public function create() {
		if(!isset($this->parameters['translationID'])) $this->parameters['translationID'] = ArticleUtil::getNextTranslationID();

		$object = call_user_func(array($this->className, 'create'), $this->parameters);

		// update search index
		SearchIndexManager::getInstance()->add('com.woltnet.wiki.article', $object->articleID, $object->message, $object->subject, $object->time, $object->userID, $object->username, $object->languageID);

		return $object;
	}

	/**
	 * Validating parameters for trashing articles.
	 */
	public function validateTrash() {
		$this->loadArticles();

		foreach ($this->articles as $article) {
			if ($article->isDeleted) {
				throw new ValidateActionException("Action is not applicable for article ".$article->articleID);
			}

			if (!$article->isTrashable()) {
				throw new PermissionDeniedException();
			}
		}
	}

	/**
	 * Trashes given articles.
	 *
	 * @return	array<array>
	 */
	public function trash() {
		foreach ($this->articles as $article) {
			$articleEditor = new ArticleEditor($article);
			$articleEditor->update(array(
				'isDeleted' => 1,
				'deleteTime' => TIME_NOW
			));
		}

		$this->unmarkItems();
	}
	
	/**
	 * Validating parameters for restoring articles.
	 */
	public function validateRestore() {
		$this->loadArticles();
		
		foreach ($this->articles as $article) {
			if (!$article->isDeleted) {
				throw new ValidateActionException("Action is not applicable for article ".$article->articleID);
			}
		
			if (!$article->isRestorable()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * Restores given articles.
	 *
	 * @return	array<array>
	 */
	public function restore() {
		foreach ($this->articles as $article) {
			$articleEditor = new ArticleEditor($article);
			$articleEditor->update(array(
				'isDeleted' => 0,
				'deleteTime' => 0
			));
		}
		
		$this->unmarkItems();
	}
	
	public function validateDelete() {
		// read objects
		if (empty($this->objects)) {
			$this->readObjects();
				
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		foreach($this->objects AS $object) {
			if(!$object->isDeletable) throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Validating for enabling articles.
	 */
	public function validateEnable() {
		$this->loadArticles();
		
		foreach ($this->articles as $article) {
			if (!$article->isActive) {
				throw new ValidateActionException("Action is not applicable for article ".$article->articleID);
			}
		}
	}
	
	/**
	 * Enables given articles.
	 * 
	 * @return	array<array>
	 */
	public function enable() {
		foreach ($this->articles as $article) {
			$articleEditor = new ArticleEditor($article);
			$articleEditor->setActive();
		}
		
		$this->unmarkItems();
	}
	
	/**
	 * Validates user access for label management.
	 */
	public function validateGetLabelManagement() {
		if (!WCF::getSession()->getPermission('mod.wiki.category.canManageLabels')) {
			throw new PermissionDeniedException();
		}
		
		$this->parameters['data']['categoryID'] = (isset($this->parameters['data']['categoryID'])) ? intval($this->parameters['data']['categoryID']) : 0;
		if (empty($this->parameters['data']['categoryID'])) {
			throw new UserInputException('categoryID');
		}
	}
	
	/**
	 * Returns the article label management.
	 *
	 * @return	array
	 */
	public function getLabelManagement() {
		WCF::getTPL()->assign(array(
			'cssClassNames' => ArticleLabel::getLabelCssClassNames(),
			'labelList' => ArticleLabel::getLabelsByCategory($this->parameters['data']['categoryID']),
			'categoryID' => $this->parameters['data']['categoryID']
		));
	
		return array(
			'actionName' => 'getLabelManagement',
			'template' => WCF::getTPL()->fetch('articleLabelManagement', 'wiki')
		);
	}

	/**
	 * Loads articles for given object ids.
	 */
	protected function loadArticles() {
		if (empty($this->objectIDs)) {
			throw new UserInputException("objectIDs");
		}

		// load articles
		$articleList = new ArticleList();
		$articleList->getConditionBuilder()->add("article.articleID IN (?)", array($this->objectIDs));
		$articleList->sqlLimit = 0;
		$articleList->readObjects();

		foreach ($articleList as $article) {
			$this->articles[$article->articleID] = $article;
		}

		if (empty($this->articles)) {
			throw new UserInputException("objectIDs");
		}
	}

	/**
	 * Marks projects as read.
	 */
	public function markAsRead() {
		if (empty($this->parameters['visitTime'])) {
			$this->parameters['visitTime'] = TIME_NOW;
		}

		if (!count($this->objects)) {
			$this->readObjects();
		}

		foreach ($this->objects as $article) {
			VisitTracker::getInstance()->trackObjectVisit('com.woltnet.wiki.article', $article->articleID, $this->parameters['visitTime']);
		}

		// reset storage
		if (WCF::getUser()->userID) {
			UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'unreadArticles', PackageDependencyHandler::getInstance()->getPackageID('com.woltnet.wiki'));
		}
	}

	/**
	 * @see wcf\data\IClipboardAction::validateUnmarkAll()
	 */
	public function validateUnmarkAll() {
		// does nothing
	}

	/**
	 * @see wcf\data\IClipboardAction::unmarkAll()
	 */
	public function unmarkAll() {
		ClipboardHandler::getInstance()->removeItems(ClipboardHandler::getInstance()->getObjectTypeID('com.woltnet.wiki.article'));
	}

	/**
	 * Unmarks articles.
	 */
	protected function unmarkItems() {
		ClipboardHandler::getInstance()->unmark(array_keys($this->articles), ClipboardHandler::getInstance()->getObjectTypeID('com.woltnet.wiki.article'));
	}
}
