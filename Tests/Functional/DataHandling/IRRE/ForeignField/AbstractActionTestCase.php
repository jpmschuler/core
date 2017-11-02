<?php
namespace TYPO3\CMS\Core\Tests\Functional\DataHandling\IRRE\ForeignField;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\StringUtility;

/**
 * Functional test for the DataHandler
 */
abstract class AbstractActionTestCase extends \TYPO3\CMS\Core\Tests\Functional\DataHandling\AbstractDataHandlerActionTestCase
{
    const VALUE_PageId = 89;
    const VALUE_PageIdTarget = 90;
    const VALUE_PageIdWebsite = 1;
    const VALUE_ContentIdFirst = 297;
    const VALUE_ContentIdLast = 298;
    const VALUE_HotelIdFirst = 3;
    const VALUE_HotelIdSecond = 4;
    const VALUE_HotelIdThird = 5;
    const VALUE_LanguageId = 1;
    const VALUE_LanguageIdSecond = 2;

    const TABLE_Page = 'pages';
    const TABLE_Content = 'tt_content';
    const TABLE_Hotel = 'tx_irretutorial_1nff_hotel';
    const TABLE_Offer = 'tx_irretutorial_1nff_offer';
    const TABLE_Price = 'tx_irretutorial_1nff_price';

    const FIELD_PageHotel = 'tx_irretutorial_hotels';
    const FIELD_ContentHotel = 'tx_irretutorial_1nff_hotels';
    const FIELD_HotelOffer = 'offers';
    const FIELD_OfferPrice = 'prices';

    /**
     * @var string
     */
    protected $scenarioDataSetDirectory = 'typo3/sysext/core/Tests/Functional/DataHandling/IRRE/ForeignField/DataSet/';

    protected function setUp()
    {
        parent::setUp();
        $this->importScenarioDataSet('LiveDefaultPages');
        $this->importScenarioDataSet('LiveDefaultElements');

        $this->setUpFrontendRootPage(
            1,
            [
                'typo3/sysext/core/Tests/Functional/Fixtures/Frontend/JsonRenderer.ts',
                'typo3/sysext/core/Tests/Functional/Fixtures/Frontend/ExtbaseJsonRenderer.ts',
            ]
        );
        $this->backendUser->workspace = 0;
    }

    /**
     * Parent content records
     */

    /**
     * @see DataSet/createParentContentRecord.csv
     */
    public function createParentContent()
    {
        $newTableIds = $this->actionService->createNewRecord(self::TABLE_Content, self::VALUE_PageId, ['header' => 'Testing #1']);
        $this->recordIds['newContentId'] = $newTableIds[self::TABLE_Content][0];
    }

    /**
     * @see DataSet/modifyParentContentRecord.csv
     */
    public function modifyParentContent()
    {
        $this->actionService->modifyRecord(self::TABLE_Content, self::VALUE_ContentIdLast, ['header' => 'Testing #1']);
    }

    /**
     * @see DataSet/deleteParentContentRecord.csv
     */
    public function deleteParentContent()
    {
        $this->actionService->deleteRecord(self::TABLE_Content, self::VALUE_ContentIdLast);
    }

    /**
     * @see DataSet/copyParentContentRecord.csv
     */
    public function copyParentContent()
    {
        $newTableIds = $this->actionService->copyRecord(self::TABLE_Content, self::VALUE_ContentIdLast, self::VALUE_PageId);
        $this->recordIds['newContentId'] = $newTableIds[self::TABLE_Content][self::VALUE_ContentIdLast];
    }

    /**
     * @see DataSet/copyParentContentToDifferentPage.csv
     */
    public function copyParentContentToDifferentPage()
    {
        $newTableIds = $this->actionService->copyRecord(self::TABLE_Content, self::VALUE_ContentIdLast, self::VALUE_PageIdTarget);
        $this->recordIds['newContentId'] = $newTableIds[self::TABLE_Content][self::VALUE_ContentIdLast];
    }

    /**
     * @see DataSet/localizeParentContentSynchronization.csv
     */
    public function localizeParentContentWithLanguageSynchronization()
    {
        $GLOBALS['TCA'][self::TABLE_Content]['columns'][self::FIELD_ContentHotel]['config']['behaviour']['allowLanguageSynchronization'] = true;
        $GLOBALS['TCA'][self::TABLE_Hotel]['columns'][self::FIELD_HotelOffer]['config']['behaviour']['allowLanguageSynchronization'] = true;
        $GLOBALS['TCA'][self::TABLE_Offer]['columns'][self::FIELD_OfferPrice]['config']['behaviour']['allowLanguageSynchronization'] = true;
        $newTableIds = $this->actionService->localizeRecord(self::TABLE_Content, self::VALUE_ContentIdLast, self::VALUE_LanguageId);
        $this->recordIds['localizedContentId'] = $newTableIds[self::TABLE_Content][self::VALUE_ContentIdLast];
    }

    /**
     * @see DataSet/localizeParentContentWAllChildrenSelect.csv
     */
    public function localizeParentContentChainLanguageSynchronizationSource()
    {
        $GLOBALS['TCA'][self::TABLE_Content]['columns'][self::FIELD_ContentHotel]['config']['behaviour']['allowLanguageSynchronization'] = true;
        $newTableIds = $this->actionService->localizeRecord(self::TABLE_Content, self::VALUE_ContentIdLast, self::VALUE_LanguageId);
        $this->recordIds['localizedContentIdFirst'] = $newTableIds[self::TABLE_Content][self::VALUE_ContentIdLast];
        $newTableIds = $this->actionService->localizeRecord(self::TABLE_Content, $this->recordIds['localizedContentIdFirst'], self::VALUE_LanguageIdSecond);
        $this->recordIds['localizedContentIdSecond'] = $newTableIds[self::TABLE_Content][$this->recordIds['localizedContentIdFirst']];
        $this->actionService->modifyRecord(
            self::TABLE_Content,
            $this->recordIds['localizedContentIdSecond'],
            ['l10n_state' => [self::FIELD_ContentHotel => 'source']]
        );
        $this->actionService->modifyRecords(
            self::VALUE_PageId,
            [
                self::TABLE_Content => ['uid' => self::VALUE_ContentIdLast, self::FIELD_ContentHotel => '5,__nextUid'],
                self::TABLE_Hotel => ['uid' => '__NEW', 'title' => 'Hotel #2'],
            ]
        );
    }

    /**
     * @see DataSet/copyParentContentToLanguageWAllChildren.csv
     */
    public function copyParentContentToLanguageWithAllChildren()
    {
        $newTableIds = $this->actionService->copyRecordToLanguage(self::TABLE_Content, self::VALUE_ContentIdLast, self::VALUE_LanguageId);
        $this->recordIds['localizedContentId'] = $newTableIds[self::TABLE_Content][self::VALUE_ContentIdLast];
    }

    /**
     * @see DataSet/localizeParentContentWAllChildren.csv
     */
    public function localizeParentContentWithAllChildren()
    {
        $newTableIds = $this->actionService->localizeRecord(self::TABLE_Content, self::VALUE_ContentIdLast, self::VALUE_LanguageId);
        $this->recordIds['localizedContentId'] = $newTableIds[self::TABLE_Content][self::VALUE_ContentIdLast];
    }

    /**
     * @see DataSet/Modify/localizeParentContentNCreateNestedChildrenWLanguageSynchronization.csv
     */
    public function localizeParentContentAndCreateNestedChildrenWithLanguageSynchronization()
    {
        $GLOBALS['TCA'][self::TABLE_Content]['columns'][self::FIELD_ContentHotel]['config']['behaviour']['allowLanguageSynchronization'] = true;
        $GLOBALS['TCA'][self::TABLE_Hotel]['columns'][self::FIELD_HotelOffer]['config']['behaviour']['allowLanguageSynchronization'] = true;
        $GLOBALS['TCA'][self::TABLE_Offer]['columns'][self::FIELD_OfferPrice]['config']['behaviour']['allowLanguageSynchronization'] = true;

        $newTableIds = $this->actionService->localizeRecord(self::TABLE_Content, self::VALUE_ContentIdLast, self::VALUE_LanguageId);
        $this->recordIds['localizedContentIdFirst'] = $newTableIds[self::TABLE_Content][self::VALUE_ContentIdLast];

        $newHotelId = StringUtility::getUniqueId('NEW');
        $newOfferId = StringUtility::getUniqueId('NEW');
        $newPriceId = StringUtility::getUniqueId('NEW');
        $dataMap = [
            self::TABLE_Content => [
                self::VALUE_ContentIdLast => [self::FIELD_ContentHotel => '5,' . $newHotelId],
            ],
            self::TABLE_Hotel => [
                $newHotelId => ['pid' => self::VALUE_PageId, 'title' => 'New Hotel #1', 'offers' => $newOfferId],
            ],
            self::TABLE_Offer => [
                $newOfferId => ['pid' => self::VALUE_PageId, 'title' => 'New Offer #1.1', 'prices' => $newPriceId],
            ],
            self::TABLE_Price => [
                $newPriceId => ['pid' => self::VALUE_PageId, 'title' => 'New Price #1.1.1'],
            ],
        ];
        $this->actionService->invoke($dataMap, []);
        $this->recordIds['newHoteId'] = $this->actionService->getDataHandler()->substNEWwithIDs[$newHotelId];
        $this->recordIds['newOfferId'] = $this->actionService->getDataHandler()->substNEWwithIDs[$newOfferId];
        $this->recordIds['newPriceId'] = $this->actionService->getDataHandler()->substNEWwithIDs[$newPriceId];
    }

    /**
     * @see DataSet/changeParentContentRecordSorting.csv
     */
    public function changeParentContentSorting()
    {
        $this->actionService->moveRecord(self::TABLE_Content, self::VALUE_ContentIdFirst, -self::VALUE_ContentIdLast);
    }

    /**
     * @see DataSet/moveParentContentRecordToDifferentPage.csv
     */
    public function moveParentContentToDifferentPage()
    {
        $this->actionService->moveRecord(self::TABLE_Content, self::VALUE_ContentIdLast, self::VALUE_PageIdTarget);
    }

    /**
     * @see DataSet/moveParentContentRecordToDifferentPageAndChangeSorting.csv
     */
    public function moveParentContentToDifferentPageAndChangeSorting()
    {
        $this->actionService->moveRecord(self::TABLE_Content, self::VALUE_ContentIdLast, self::VALUE_PageIdTarget);
        $this->actionService->moveRecord(self::TABLE_Content, self::VALUE_ContentIdFirst, -self::VALUE_ContentIdLast);
    }

    /**
     * Page records
     */

    /**
     * @see DataSet/modifyPageRecord.csv
     */
    public function modifyPage()
    {
        $this->actionService->modifyRecord(self::TABLE_Page, self::VALUE_PageId, ['title' => 'Testing #1']);
    }

    /**
     * @see DataSet/deletePageRecord.csv
     */
    public function deletePage()
    {
        $this->actionService->deleteRecord(self::TABLE_Page, self::VALUE_PageId);
    }

    /**
     * @see DataSet/copyPageRecord.csv
     */
    public function copyPage()
    {
        $newTableIds = $this->actionService->copyRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdTarget);
        $this->recordIds['newPageId'] = $newTableIds[self::TABLE_Page][self::VALUE_PageId];
        $this->recordIds['newContentIdFirst'] = $newTableIds[self::TABLE_Content][self::VALUE_ContentIdFirst];
        $this->recordIds['newContentIdLast'] = $newTableIds[self::TABLE_Content][self::VALUE_ContentIdLast];
    }

    /**
     * @see DataSet/copyPageWHotelBeforeParentContent.csv
     */
    public function copyPageWithHotelBeforeParentContent()
    {
        // Ensure hotels get processed first
        $GLOBALS['TCA'] = array_merge(
            [self::TABLE_Hotel => $GLOBALS['TCA'][self::TABLE_Hotel]],
            $GLOBALS['TCA']
        );

        $newTableIds = $this->actionService->copyRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdTarget);
        $this->recordIds['newPageId'] = $newTableIds[self::TABLE_Page][self::VALUE_PageId];
        $this->recordIds['newContentIdFirst'] = $newTableIds[self::TABLE_Content][self::VALUE_ContentIdFirst];
        $this->recordIds['newContentIdLast'] = $newTableIds[self::TABLE_Content][self::VALUE_ContentIdLast];
    }

    /**
     * IRRE Child Records
     */

    /**
     * @see DataSet/createParentContentRecordWithHotelAndOfferChildRecords.csv
     */
    public function createParentContentWithHotelAndOfferChildren()
    {
        $newTableIds = $this->actionService->createNewRecords(
            self::VALUE_PageId,
            [
                self::TABLE_Content => ['header' => 'Testing #1', self::FIELD_ContentHotel => '__nextUid'],
                self::TABLE_Hotel => ['title' => 'Hotel #1', self::FIELD_HotelOffer => '__nextUid'],
                self::TABLE_Offer => ['title' => 'Offer #1'],
            ]
        );
        $this->recordIds['newContentId'] = $newTableIds[self::TABLE_Content][0];
    }

    /**
     * @see DataSet/createAndCopyParentContentRecordWithHotelAndOfferChildRecords.csv
     */
    public function createAndCopyParentContentWithHotelAndOfferChildren()
    {
        $newTableIds = $this->actionService->createNewRecords(
            self::VALUE_PageId,
            [
                self::TABLE_Content => ['header' => 'Testing #1', self::FIELD_ContentHotel => '__nextUid'],
                self::TABLE_Hotel => ['title' => 'Hotel #1', self::FIELD_HotelOffer => '__nextUid'],
                self::TABLE_Offer => ['title' => 'Offer #1'],
            ]
        );
        $this->recordIds['newContentId'] = $newTableIds[self::TABLE_Content][0];
        $this->recordIds['newHotelId'] = $newTableIds[self::TABLE_Hotel][0];
        $copiedTableIds = $this->actionService->copyRecord(self::TABLE_Content, $this->recordIds['newContentId'], self::VALUE_PageId);
        $this->recordIds['copiedContentId'] = $copiedTableIds[self::TABLE_Content][$this->recordIds['newContentId']];
        $this->recordIds['copiedHotelId'] = $copiedTableIds[self::TABLE_Hotel][$this->recordIds['newHotelId']];
    }

    /**
     * @see DataSet/createAndLocalizeParentContentRecordWithHotelAndOfferChildRecords.csv
     */
    public function createAndLocalizeParentContentWithHotelAndOfferChildren()
    {
        $newTableIds = $this->actionService->createNewRecords(
            self::VALUE_PageId,
            [
                self::TABLE_Content => ['header' => 'Testing #1', self::FIELD_ContentHotel => '__nextUid'],
                self::TABLE_Hotel => ['title' => 'Hotel #1', self::FIELD_HotelOffer => '__nextUid'],
                self::TABLE_Offer => ['title' => 'Offer #1'],
            ]
        );
        $this->recordIds['newContentId'] = $newTableIds[self::TABLE_Content][0];
        $this->recordIds['newHotelId'] = $newTableIds[self::TABLE_Hotel][0];
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Content, $this->recordIds['newContentId'], self::VALUE_LanguageId);
        $this->recordIds['localizedContentId'] = $localizedTableIds[self::TABLE_Content][$this->recordIds['newContentId']];
    }

    /**
     * @see DataSet/createAndLocalizeParentContentRecordWithHotelAndOfferChildRecords.csv
     */
    public function createAndLocalizeParentContentWithHotelAndOfferChildrenWithoutSortByConfiguration()
    {
        unset($GLOBALS['TCA'][self::TABLE_Hotel]['ctrl']['sortby']);
        $newTableIds = $this->actionService->createNewRecords(
            self::VALUE_PageId,
            [
                self::TABLE_Content => ['header' => 'Testing #1', self::FIELD_ContentHotel => '__nextUid'],
                self::TABLE_Hotel => ['title' => 'Hotel #1', self::FIELD_HotelOffer => '__nextUid'],
                self::TABLE_Offer => ['title' => 'Offer #1'],
            ]
        );
        $this->recordIds['newContentId'] = $newTableIds[self::TABLE_Content][0];
        $this->recordIds['newHotelId'] = $newTableIds[self::TABLE_Hotel][0];
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Content, $this->recordIds['newContentId'], self::VALUE_LanguageId);
        $this->recordIds['localizedContentId'] = $localizedTableIds[self::TABLE_Content][$this->recordIds['newContentId']];
    }

    /**
     * @see DataSet/modifyOnlyHotelChildRecord.csv
     */
    public function modifyOnlyHotelChild()
    {
        $this->actionService->modifyRecord(self::TABLE_Hotel, 4, ['title' => 'Testing #1']);
    }

    /**
     * @see DataSet/modifyParentRecordAndChangeHotelChildRecordsSorting.csv
     */
    public function modifyParentAndChangeHotelChildrenSorting()
    {
        $this->actionService->modifyRecord(self::TABLE_Content, self::VALUE_ContentIdFirst, [self::FIELD_ContentHotel => '4,3']);
    }

    /**
     * @see DataSet/modifyParentRecordWithHotelChildRecord.csv
     */
    public function modifyParentWithHotelChild()
    {
        $this->actionService->modifyRecords(
            self::VALUE_PageId,
            [
                self::TABLE_Content => ['uid' => self::VALUE_ContentIdFirst, self::FIELD_ContentHotel => '3,4'],
                self::TABLE_Hotel => ['uid' => 4, 'title' => 'Testing #1'],
            ]
        );
    }

    /**
     * @see DataSet/modifyParentRecordAndAddHotelChildRecord.csv
     */
    public function modifyParentAndAddHotelChild()
    {
        $this->actionService->modifyRecords(
            self::VALUE_PageId,
            [
                self::TABLE_Content => ['uid' => self::VALUE_ContentIdLast, self::FIELD_ContentHotel => '5,__nextUid'],
                self::TABLE_Hotel => ['uid' => '__NEW', 'title' => 'Hotel #2'],
            ]
        );
    }

    /**
     * @see DataSet/modifyParentRecordAndDeleteHotelChildRecord.csv
     */
    public function modifyParentAndDeleteHotelChild()
    {
        $this->actionService->modifyRecord(
            self::TABLE_Content,
            self::VALUE_ContentIdFirst,
            [self::FIELD_ContentHotel => '3'],
            [self::TABLE_Hotel => [4]]
        );
    }

    public function localizePageWithLocalizationExclude()
    {
        $GLOBALS['TCA'][self::TABLE_Page]['columns'][self::FIELD_PageHotel]['l10n_mode'] = 'exclude';
        // in these test cases we expect new pages not to be hidden in order to
        // verify proper overlaying behavior during the frontend render process
        $GLOBALS['TCA'][self::TABLE_Page]['columns']['hidden']['config']['default'] = 0;
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->recordIds['localizedPageId'] = $localizedTableIds[self::TABLE_Page][self::VALUE_PageId];
    }

    public function localizePageAndAddHotelChildWithLocalizationExclude()
    {
        $GLOBALS['TCA'][self::TABLE_Page]['columns'][self::FIELD_PageHotel]['l10n_mode'] = 'exclude';
        // in these test cases we expect new pages not to be hidden in order to
        // verify proper overlaying behavior during the frontend render process
        $GLOBALS['TCA'][self::TABLE_Page]['columns']['hidden']['config']['default'] = 0;
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->recordIds['localizedPageId'] = $localizedTableIds[self::TABLE_Page][self::VALUE_PageId];
        $this->actionService->modifyRecords(
            self::VALUE_PageId,
            [
                self::TABLE_Page => ['uid' => self::VALUE_PageId, self::FIELD_PageHotel => '2,__nextUid'],
                self::TABLE_Hotel => ['uid' => '__NEW', 'title' => 'Hotel #007'],
            ]
        );
    }

    public function localizePageWithLanguageSynchronization()
    {
        // in these test cases we expect new pages not to be hidden in order to
        // verify proper overlaying behavior during the frontend render process
        $GLOBALS['TCA'][self::TABLE_Page]['columns']['hidden']['config']['default'] = 0;
        $GLOBALS['TCA'][self::TABLE_Page]['columns'][self::FIELD_PageHotel]['config']['behaviour']['allowLanguageSynchronization'] = true;
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->recordIds['localizedPageId'] = $localizedTableIds[self::TABLE_Page][self::VALUE_PageId];
    }

    public function localizePageAndAddHotelChildWithLanguageSynchronization()
    {
        // in these test cases we expect new pages not to be hidden in order to
        // verify proper overlaying behavior during the frontend render process
        $GLOBALS['TCA'][self::TABLE_Page]['columns']['hidden']['config']['default'] = 0;
        $GLOBALS['TCA'][self::TABLE_Page]['columns'][self::FIELD_PageHotel]['config']['behaviour']['allowLanguageSynchronization'] = true;
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->recordIds['localizedPageId'] = $localizedTableIds[self::TABLE_Page][self::VALUE_PageId];
        $this->actionService->modifyRecords(
            self::VALUE_PageId,
            [
                self::TABLE_Page => ['uid' => self::VALUE_PageId, self::FIELD_PageHotel => '2,__nextUid'],
                self::TABLE_Hotel => ['uid' => '__NEW', 'title' => 'Hotel #007'],
            ]
        );
    }

    public function localizePageAndAddMonoglotHotelChildWithLanguageSynchronization()
    {
        // in these test cases we expect new pages not to be hidden in order to
        // verify proper overlaying behavior during the frontend render process
        $GLOBALS['TCA'][self::TABLE_Page]['columns']['hidden']['config']['default'] = 0;
        unset($GLOBALS['TCA'][self::TABLE_Hotel]['ctrl']['languageField']);
        unset($GLOBALS['TCA'][self::TABLE_Hotel]['ctrl']['transOrigPointerField']);
        $GLOBALS['TCA'][self::TABLE_Page]['columns'][self::FIELD_PageHotel]['config']['behaviour']['allowLanguageSynchronization'] = true;
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->recordIds['localizedPageId'] = $localizedTableIds[self::TABLE_Page][self::VALUE_PageId];
        $this->actionService->modifyRecords(
            self::VALUE_PageId,
            [
                self::TABLE_Page => ['uid' => self::VALUE_PageId, self::FIELD_PageHotel => '2,__nextUid'],
                self::TABLE_Hotel => ['uid' => '__NEW', 'title' => 'Hotel #007'],
            ]
        );
    }

    public function localizeAndCopyPageWithLanguageSynchronization()
    {
        // in these test cases we expect new pages not to be hidden in order to
        // verify proper overlaying behavior during the frontend render process
        $GLOBALS['TCA'][self::TABLE_Page]['columns']['hidden']['config']['default'] = 0;
        $GLOBALS['TCA'][self::TABLE_Page]['columns'][self::FIELD_PageHotel]['config']['behaviour']['allowLanguageSynchronization'] = true;
        $localizedTableIds = $this->actionService->localizeRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_LanguageId);
        $this->recordIds['localizedPageId'] = $localizedTableIds[self::TABLE_Page][self::VALUE_PageId];
        $newTableIds = $this->actionService->copyRecord(self::TABLE_Page, self::VALUE_PageId, self::VALUE_PageIdTarget);
        $this->recordIds['newPageId'] = $newTableIds[self::TABLE_Page][self::VALUE_PageId];
    }
}
