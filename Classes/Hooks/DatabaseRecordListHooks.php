<?php
namespace ChristianEssl\Impersonate\Hooks;

/***
 *
 * This file is part of the "Impersonate" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2019 Christian Eßl <indy.essl@gmail.com>, https://christianessl.at
 *
 ***/

use ChristianEssl\Impersonate\Utility\ConfigurationUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;
use TYPO3\CMS\Recordlist\RecordList\RecordListHookInterface;

/**
 * Hooks for DatabaseRecordList, implementing the icons for impersonating a frontend user
 */
class DatabaseRecordListHooks implements RecordListHookInterface
{
    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
    }

    /**
     * Modifies Web>List clip icons (copy, cut, paste, etc.) of a displayed row
     *
     * @param string $table The current database table
     * @param array $row The current record row
     * @param array $cells The default clip-icons to get modified
     * @param DatabaseRecordList $parentObject Instance of calling object
     *
     * @return array The modified clip-icons
     */
    public function makeClip($table, $row, $cells, &$parentObject)
    {
        return $cells;
    }

    /**
     * Modifies Web>List control icons of a displayed row
     *
     * @param string $table The current database table
     * @param array $row The current record row
     * @param array $cells The default control-icons to get modified
     * @param object $parentObject Instance of calling object
     *
     * @return array The modified control-icons
     */
    public function makeControl($table, $row, $cells, &$parentObject)
    {
        if ($table === 'fe_users') {
            $this->addImpersonateButton($cells, $row);
        }
        return $cells;
    }

    /**
     * Modifies Web>List header row columns/cells
     *
     * @param string $table The current database table
     * @param array $currentIdList Array of the currently displayed uids of the table
     * @param array $headerColumns An array of rendered cells/columns
     * @param object $parentObject Instance of calling (parent) object
     *
     * @return array Array of modified cells/columns
     */
    public function renderListHeader($table, $currentIdList, $headerColumns, &$parentObject)
    {
        return $headerColumns;
    }

    /**
     * Modifies Web>List header row clipboard/action icons
     *
     * @param string $table The current database table
     * @param array $currentIdList Array of the currently displayed uids of the table
     * @param array $cells An array of the current clipboard/action icons
     * @param object $parentObject Instance of calling (parent) object
     *
     * @return array Array of modified clipboard/action icons
     */
    public function renderListHeaderActions($table, $currentIdList, $cells, &$parentObject)
    {
        return $cells;
    }

    /**
     * @param array $cells
     * @param array $userRow
     */
    protected function addImpersonateButton(&$cells, $userRow)
    {
        //@todo refactor

        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Impersonate/ImpersonateButton', 'function(ImpersonateButton) {
			ImpersonateButton.init();
		}');

        $userId = $userRow['uid'];
        $pageId = ConfigurationUtility::getRedirectPageId();
        $previewUrl = $this->getPreviewUrl($pageId);
        $buttonText = $this->translate('button.impersonate');
        $iconMarkup = $this->iconFactory->getIcon('actions-system-backend-user-switch', Icon::SIZE_SMALL)->render();

        $button = '
            <a  class="btn btn-default t3-impersonate-button" 
                data-uid="'.$userId.'"
                href="'.$previewUrl.'" target="_blank" 
                title="'.$buttonText.'">
	                '.$iconMarkup.'	
            </a>';

        $cells['impersonate'] = $button;
    }

    /**
     * @param integer $pageId
     *
     * @return string
     */
    protected function getPreviewUrl($pageId)
    {
        $switchFocus = true;
        return BackendUtility::getPreviewUrl(
            $pageId,
            '',
            null,
            '',
            '',
            '',
            $switchFocus
        );
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function translate($key)
    {
        return $GLOBALS['LANG']->sL('LLL:EXT:impersonate/Resources/Private/Language/locallang.xlf:'.$key);
    }
}