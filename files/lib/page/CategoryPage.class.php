<?php
namespace wiki\page;
use wiki\data\category\WikiCategoryNodeList;
use wiki\data\category\WikiCategory;
use wiki\data\article\CategoryArticleList;

use wcf\page\SortablePage;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\dashboard\DashboardHandler;
use wcf\system\user\collapsible\content\UserCollapsibleContentHandler;
use wcf\system\WCF;

/**
 * Displays category page
 *
 * @author	Rene Gessinger (NurPech)
 * @copyright	2012 woltnet
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltnet.wiki
 * @subpackage	page
 * @category 	WoltNet - Wiki
 */
class CategoryPage extends SortablePage {
	/**
	 * given categoryID
	 * 
	 * @var integer
	 */
	public $categoryID = 0;
	
	/**
	 * WikiCategory-Object of the given category
	 * 
	 * @var wiki\datata\category\WikiCategory
	 */
	public $category = null;
	
	/**
	 * category node list
	 * 
	 * @var wiki\data\category\WikiCategoryNodeList
	 */
	public $categoryNodeList = null;
	
	/**
	 * @see wcf\page\MultipleLinkPage::$itemsPerPage
	 */
	public $itemsPerPage = WIKI_CATEGORY_ARTICLES_PER_PAGE;
	
	/**
	 * @see wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = WIKI_CATEGORY_DEFAULT_SORT_FIELD;
	
	/**
	 * @see wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortOrder = WIKI_CATEGORY_DEFAULT_SORT_ORDER;
	
	/**
	 * @see wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('subject', 'username', 'time');
	
	/**
	 * @see wcf\page\AbstractPage::$enableTracking
	 */
	public $enableTracking = true;
	
	/**
	 * @see wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wiki\data\article\CategoryArticleList';
	
	/**
	 * objectTypeName for Wiki Categoires
	 *
	 * @var string
	 */
	public $objectTypeName = 'com.woltnet.wiki.category';
	
	/**
	 * @see wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
	
		if (isset($_REQUEST['id'])) $this->categoryID = intval($_REQUEST['id']);
	
		$category = CategoryHandler::getInstance()->getCategory($this->categoryID);
		
		if($category !== null) $this->category = new WikiCategory($category);
	
		if($this->category === null || !$this->category->categoryID) {
			throw new IllegalLinkException();
		}
	
		// check permissions
		if (!$this->category->isAccessible()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @see wcf\page\AbstractPage::readData()
	 */
	public function readData() {
		parent::readData();
	
		// get node tree
		$this->categoryNodeList = new WikiCategoryNodeList($this->objectTypeName, $this->category->categoryID);
		$this->categoryNodeList->setMaxDepth(0);
	}
	
	/**
	 * @see wcf\page\AbstractPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
	
		// load boxes
		DashboardHandler::getInstance()->loadBoxes('com.woltnet.wiki.CategoryPage', $this);
	
		WCF::getTPL()->assign(array(
				'categoryList' => $this->categoryNodeList,
				'category'	=> $this->category,
				'sidebarCollapsed' => UserCollapsibleContentHandler::getInstance()->isCollapsed('com.woltlab.wcf.collapsibleSidebar', 'com.woltnet.wiki.category'),
				'sidebarName' => 'com.woltnet.wiki.category'
		));
	}
	
	/**
	 * @see wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function initObjectList() {
		$this->objectList = new CategoryArticleList($this->category, $this->categoryID);
	}
	
	/**
	 * Reads object list.
	 */
	protected function readObjects() {
		$this->objectList->sqlLimit = $this->sqlLimit;
		$this->objectList->sqlOffset = $this->sqlOffset;
		if ($this->sqlOrderBy) $this->objectList->sqlOrderBy = $this->sqlOrderBy;
		$this->objectList->readObjects();
	}
	
	/**
	 * @see wcf\page\ITrackablePage::getObjectID()
	 */
	public function getObjectID() {
		return $this->categoryID;
	}
	
	/**
	 * @see wcf\page\ITrackablePage::getObjectType()
	 */
	public function getObjectType() {
		return 'com.woltlab.wiki.category';
	}
}