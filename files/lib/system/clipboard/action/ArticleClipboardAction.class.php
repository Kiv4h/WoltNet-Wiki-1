<?php
namespace wiki\system\clipboard\action;
use wcf\system\clipboard\ClipboardEditorItem;
use wcf\system\clipboard\action\IClipboardAction;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Prepares clipboard editor items for articles.
 * 
 * @author	Rene Gessinger (NurPech), Jean-Marc Licht
 * @copyright	2012 WoltNet
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltnet.wiki
 * @subpackage	system.clipboard.action
 * @category	WoltNet Wiki
 */
class ArticleClipboardAction implements IClipboardAction {
	/**
	 * list of conversations
	 * 
	 * @var	array<wiki\data\article\Article>
	 */
	public $articles = null;
	
	/**
	 * @see	wcf\system\clipboard\action\IClipboardAction::getTypeName()
	 */
	public function getTypeName() {
		return 'com.woltnet.wiki.article';
	}
	
	/**
	 * @see	wcf\system\clipboard\action\IClipboardAction::getClassName()
	 */
	public function getClassName() {
		return 'wiki\data\article\ArticleAction';
	}
	
	/**
	 * @see	wcf\system\clipboard\action\IClipboardAction::execute()
	 */
	public function execute(array $objects, $actionName, array $typeData = array()) {
		// check if no article was accessible
		if (empty($this->articles)) {
			$this->articles = $this->loadCategories($objects);
		}
		
		$item = new ClipboardEditorItem();
		
		switch ($actionName) {
			case 'assignLabel':
				$article = current($this->articles);
				if(is_object($article)) {
					// check if category has labels
					$sql = "SELECT	COUNT(*) AS count
						FROM	wiki".WCF_N."_article_label
						WHERE	categoryID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute(array($article->categoryID));
					$row = $statement->fetchArray();
					if ($row['count'] == 0) {
						return null;
					}
				}
				
				$item->addParameter('objectIDs', array_keys($this->articles));
				$item->addParameter('categoryID', $article->categoryID);
				$item->setName('article.assignLabel');
			break;
			
			case 'trash':
				$articleIDs = $this->validateTrash();
				if (empty($articleIDs)) {
					return null;
				}
				
				$item->addParameter('objectIDs', $articleIDs);
				$item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.project.article.confirmMessage', array('count' => count($articleIDs))));
				$item->addParameter('actionName', 'trash');
				$item->addParameter('className', 'wiki\data\article\ArticleAction');
				$item->setName('article.trash');
			break;
			
			case 'delete':
				$articleIDs = $this->validateDelete();
				if (empty($articleIDs)) {
					return null;
				}
			
				$item->addParameter('objectIDs', $articleIDs);
				$item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.article.delete.confirmMessage', array('count' => count($articleIDs))));
				$item->addParameter('actionName', 'delete');
				$item->addParameter('className', 'wiki\data\article\ArticleAction');
				$item->setName('article.delete');
			break;
			
			case 'restore':
				$articleIDs = $this->validateRestore();
				if (empty($articleIDs)) {
					return null;
				}
			
				$item->addParameter('objectIDs', $articleIDs);
				$item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.project.restore.confirmMessage', array('count' => count($articleIDs))));
				$item->addParameter('actionName', 'restore');
				$item->addParameter('className', 'wiki\data\article\ArticleAction');
				$item->setName('article.restore');
			break;
			
			case 'enable':
				$articleIDs = $this->validateEnable();
				if (empty($articleIDs)) {
					return null;
				}
				
				$item->addParameter('objectIDs', $articleIDs);
				$item->addParameter('actionName', 'enable');
				$item->addParameter('className', 'wiki\data\article\ArticleAction');
				$item->setName('article.enable');
			break;
			
			default:
				throw new SystemException("Unknown action '".$actionName."'");
			break;
		}
		
		return $item;
	}
	
	/**
	 * Validates articles for restoring and returns ids.
	 *
	 * @TODO: implement permission check
	 * @return	array<integer>
	 */
	public function validateRestore() {
		$articleIDs = array();
		
		foreach ($this->articles as $article) {
			if ($article->isDeleted) {
				$articleIDs[] = $article->articleID;
			}
		}
		
		return $articleIDs;
	}
	
	/**
	 * Validates articles for deleting and returns ids.
	 *
	 * @TODO: implement permission check
	 * @return	array<integer>
	 */
	public function validateDelete() {
		$articleIDs = array();
		
		foreach ($this->articles as $article) {
			if ($article->isDeleted) {
				$articleIDs[] = $article->articleID;
			}
		}
		
		return $articleIDs;
	}
	
	/**
	 * Validates articles for trashing and returns ids.
	 *
	 * @TODO: implement permission check
	 * @return	array<integer>
	 */
	public function validateTrash() {
		$articleIDs = array();
		
		foreach ($this->articles as $article) {
			if (!$article->isDeleted) {
				$articleIDs[] = $article->articleID;
			}
		}
		
		return $articleIDs;
	}
	
	/**
	 * validates articles for enabling and returns ids.
	 *
	 * @TODO: implement permission check
	 * @return	array<integer>
	 */
	public function validateEnable() {
		$articleIDs = array();
		
		foreach ($this->articles as $article) {
			if (!$article->isActive) {
				$articleIDs[] = $article->articleID;
			}
		}
		
		return $articleIDs;
	}
	
	/**
	 * load categories for given articles
	 * 
	 * @param	array<wiki\data\article\Article>	$articles
	 * @return	array<wiki\data\article\Article>
	 */
	protected function loadCategories(array $articles) {
		// TODO: get categories to article for permission check
		
		return $articles;
	}
	
	/**
	 * @see	wcf\system\clipboard\action\IClipboardAction::getEditorLabel()
	 */
	public function getEditorLabel(array $objects) {
		return WCF::getLanguage()->getDynamicVariable('wiki.clipboard.label.article.marked', array('count' => count($objects)));
	}
}
