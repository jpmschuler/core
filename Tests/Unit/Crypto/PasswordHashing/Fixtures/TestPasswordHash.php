<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\Tests\Unit\Crypto\PasswordHashing\Fixtures;

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

/**
 * Fixture salt class to check if constructor is called with options
 */
class TestPasswordHash
{
    /**
     * TestPasswordHash constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if ($options === [ 'foo' => 'bar' ]) {
            throw new \RuntimeException('This should be thrown', 1533950385);
        }
    }
}
