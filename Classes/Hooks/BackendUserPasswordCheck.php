<?php
declare(strict_types=1);

namespace TYPO3\CMS\Core\Hooks;

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

use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * DataHandler hook to ensure that a be_user always has a username + password set if newly-created.
 *
 * @internal This class is a hook implementation and is not part of the TYPO3 Core API.
 */
class BackendUserPasswordCheck
{
    /**
     * @var Random
     */
    protected $random;

    public function __construct()
    {
        $this->random = GeneralUtility::makeInstance(Random::class);
    }

    /**
     * @param array $incomingFieldArray
     * @param string $table
     * @param string $id
     * @param DataHandler $dataHandler
     */
    public function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, DataHandler $dataHandler)
    {
        // Not within be_users
        if ($table !== 'be_users') {
            return;
        }
        // Existing record, nothing to change
        if (MathUtility::canBeInterpretedAsInteger($id)) {
            return;
        }
        if ($dataHandler->isImporting) {
            return;
        }
        if (!isset($incomingFieldArray['password']) || (string)$incomingFieldArray['password'] === '') {
            $incomingFieldArray['password'] = GeneralUtility::hmac($id, $this->random->generateRandomBytes(20));
        }
        if (!isset($incomingFieldArray['username']) || (string)$incomingFieldArray['username'] === '') {
            $incomingFieldArray['username'] = 'autogenerated-' . GeneralUtility::shortMD5($id);
        }
    }
}
