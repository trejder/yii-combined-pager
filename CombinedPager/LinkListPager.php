<?php

/**
 * Set of pagination-related classes used for fixing some minor bugs that original
 * pagination component has and for adding some new features that lacks in default
 * solution.
 * 
 * Based on CBasePage, CLinkPager, CListPager and CPagination classes from Yii.
 */

class LinkListPager extends CBasePager
{
	/**
         * This class is an exact copy of CLinkPager extended by introducing class
         * CListPager into it. This is done this way (by copying code, not by extending
         * existing classes) because each CGridView can have ONLY ONE paging component
         * and we want to provide it with the one that will combine functionallity of 
         * both CLinkPager and CListPager.
         * 
         * For optimization (code enshorting) and for better expressing, what was
         * changed - all original comments were removed.
         * 
         * For the description of methods and variables declared in this class, 
         * look into description of coresponding method in either CLinkPager \
         * and CListPager.
         */
        const CSS_FIRST_PAGE = 'first';
	const CSS_LAST_PAGE = 'last';
	const CSS_PREVIOUS_PAGE = 'previous';
	const CSS_NEXT_PAGE = 'next';
	const CSS_INTERNAL_PAGE = 'page';
	const CSS_HIDDEN_PAGE = 'hidden';
	const CSS_SELECTED_PAGE = 'selected';
        
	public $maxButtonCount = 10;
	public $nextPageLabel;
	public $prevPageLabel;
	public $firstPageLabel;
	public $lastPageLabel;
	public $header;
	public $footer = ' &mdash; ';
	public $cssFile;
	public $htmlOptions = array();
	public $promptText;
	public $pageTextFormat;

        public function init()
	{
		if($this->nextPageLabel === null)
			$this->nextPageLabel = Yii::t('yii', 'Next &gt;');
		if($this->prevPageLabel === null)
			$this->prevPageLabel = Yii::t('yii', '&lt; Previous');
		if($this->firstPageLabel === null)
			$this->firstPageLabel = Yii::t('yii', '&lt;&lt; First');
		if($this->lastPageLabel === null)
			$this->lastPageLabel = Yii::t('yii', 'Last &gt;&gt;');
		if($this->header === null)
			$this->header = Yii::t('yii', 'Go to page: ');

		if(!isset($this->htmlOptions['id']))
			$this->htmlOptions['id'] = $this->getId();
		if(!isset($this->htmlOptions['class']))
			$this->htmlOptions['class'] = 'yiiPager';
                
		if($this->promptText!==null)
			$this->htmlOptions['prompt']=$this->promptText;
		if(!isset($this->htmlOptions['onchange']))
			$this->htmlOptions['onchange']="if(this.value!='') {window.location=this.value;};";
	}

	public function run()
	{
		$this->registerClientScript();
		$buttons = $this->createPageButtons();
                
		if(!empty($buttons))
                {
                        echo($this->header);
                        echo(CHtml::tag('ul', $this->htmlOptions, implode("\n", $buttons)));
                        echo($this->footer);
                }
                
		if(($pageCount=$this->getPageCount())<=1) return;
		$pages=array();
		for($i=0;$i<$pageCount;++$i)
			$pages[$this->createPageUrl($i)]=$this->generatePageText($i);
		$selection=$this->createPageUrl($this->getCurrentPage());
                
		echo CHtml::dropDownList($this->getId(),$selection,$pages,$this->htmlOptions);
	}

	protected function createPageButtons()
	{
		if(($pageCount = $this->getPageCount()) <= 1)
			return array();

		list($beginPage, $endPage) = $this->getPageRange();
		$currentPage = $this->getCurrentPage(false); // currentPage is calculated in getPageRange()
		$buttons = array();

		// first page
		$buttons[] = $this->createPageButton($this->firstPageLabel, 0, self::CSS_FIRST_PAGE, $currentPage <= 0, false);

		// prev page
		if(($page = $currentPage -1)<0)
			$page = 0;
		$buttons[] = $this->createPageButton($this->prevPageLabel, $page, self::CSS_PREVIOUS_PAGE, $currentPage <= 0, false);

		// internal pages
		for($i = $beginPage;$i <= $endPage;++$i)
			$buttons[] = $this->createPageButton($i+1, $i, self::CSS_INTERNAL_PAGE, false, $i == $currentPage);

		// next page
		if(($page = $currentPage+1) >= $pageCount -1)
			$page = $pageCount -1;
		$buttons[] = $this->createPageButton($this->nextPageLabel, $page, self::CSS_NEXT_PAGE, $currentPage >= $pageCount -1, false);

		// last page
		$buttons[] = $this->createPageButton($this->lastPageLabel, $pageCount -1, self::CSS_LAST_PAGE, $currentPage >= $pageCount -1, false);

		return $buttons;
	}

	protected function createPageButton($label, $page, $class, $hidden, $selected)
	{
		/**
                 * Here we do some private tweak-ups to have CLinkPager part looking
                 * and working just as we want it to look and work.
                 */
                
                $title = 'Strona nr '.($page + 1);
                
                if($class == self::CSS_FIRST_PAGE)
                {
                        $label = '<<';
                        $title = ($hidden) ? '' : 'Pierwsza strona';
                }
                
                if($class == self::CSS_LAST_PAGE)
                {
                        $label = '>>';
                        $title = ($hidden) ? '' : 'Ostatnia strona';
                }
                
                if($class == self::CSS_PREVIOUS_PAGE)
                {
                        $label = '<';
                        $title = ($hidden) ? '' : 'Poprzednia strona';
                }
                
                if($class == self::CSS_NEXT_PAGE)
                {
                        $label = '>';
                        $title = ($hidden) ? '' : 'Następna strona';
                }
                
                if($hidden || $selected) $class .= ' '.($hidden ? self::CSS_HIDDEN_PAGE : self::CSS_SELECTED_PAGE);
                
                $button = '<li title="'.$title.'" class="'.$class.'">';
                $button.= (!$hidden) ? CHtml::link($label, $this->createPageUrl($page)) : '<span>'.$label.'</span>';
                $button.= '</li>';
                
		return $button;
	}

	protected function getPageRange()
	{
		$currentPage = $this->getCurrentPage();
		$pageCount = $this->getPageCount();

		$beginPage = max(0, $currentPage-(int)($this->maxButtonCount/2));
		if(($endPage = $beginPage+$this->maxButtonCount -1) >= $pageCount)
		{
			$endPage = $pageCount -1;
			$beginPage = max(0, $endPage-$this->maxButtonCount+1);
		}
		return array($beginPage, $endPage);
	}
        
	protected function generatePageText($page)
	{
		if($this->pageTextFormat!==null)
			return sprintf($this->pageTextFormat,$page+1);
		else
			return $page+1;
	}

	public function registerClientScript()
	{
		if($this->cssFile !== false)
			self::registerCssFile($this->cssFile);
	}

	public static function registerCssFile($url = null)
	{
		if($url === null)
			$url = CHtml::asset(Yii::getPathOfAlias('ext.LinkListPager.pager').'.css');
		Yii::app()->getClientScript()->registerCssFile($url);
	}
}

?>