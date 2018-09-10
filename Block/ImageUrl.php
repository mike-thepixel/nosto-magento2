<?php
/**
 * Copyright (c) 2017, Nosto Solutions Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2017 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 *
 */

namespace Nosto\Tagging\Block;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Nosto\Tagging\Helper\Scope as NostoHelperScope;
use Nosto\Tagging\Helper\Account as NostoHelperAccount;
use Nosto\Tagging\Logger\Logger as NostoLogger;

/**
 * ImageUrl block used for getting the image url
 */
class ImageUrl extends Template
{
    const NOSTO_ACCOUNT_PLACEHOLDER = '@NOSTO_ACCOUNT@';
    const EMAIL_PLACEHOLDER = '@EMAIL@';
    const URL_TEMPLATE = 'url_template';

    private $nostoHelperScope;
    private $nostoHelperAccount;
    private $logger;
    const ORDER = 'order';
    const CUSTOMER_EMAIL = 'customer_email';

    /**
     * Constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param NostoHelperScope $nostoHelperScope
     * @param NostoHelperAccount $nostoHelperAccount
     * @param NostoLogger $logger
     * @param array $data
     */
    public function __construct(
        Context $context,
        NostoHelperScope $nostoHelperScope,
        NostoHelperAccount $nostoHelperAccount,
        NostoLogger $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->nostoHelperScope = $nostoHelperScope;
        $this->nostoHelperAccount = $nostoHelperAccount;
        $this->logger = $logger;
    }

    /**
     * Format the image url
     *
     * @return string
     */
    public function _toHtml()
    {
        $store = $this->nostoHelperScope->getStore(true);
        $account = $this->nostoHelperAccount->findAccount($store);

        if (!$account || !$account->getName()) {
            return '';
        }

        $urlTemplate = $this->getData(self::URL_TEMPLATE);
        if (!$urlTemplate) {
            $this->logger->error('url_template parameter is missing or it does not have NOSTO_ACCOUNT_PLACEHOLDER');
            return '';
        } elseif (!stripos($urlTemplate, self::NOSTO_ACCOUNT_PLACEHOLDER)) {
            $this->logger->error('NOSTO_ACCOUNT_PLACEHOLDER (@NOSTO_ACCOUNT@) is missing from url template');
            return '';
        } elseif (!stripos($urlTemplate, self::EMAIL_PLACEHOLDER)) {
            $this->logger->error('EMAIL_PLACEHOLDER (@EMAIL@) is missing from url template');
            return '';
        }

        $order = $this->getData(self::ORDER);
        if (!$order) {
            $this->logger->error('order parameter is missing');
            return '';
        } elseif (!$order->getData(self::CUSTOMER_EMAIL)) {
            $this->logger->error('customer_email is missing');
            return '';
        }

        $src = str_replace(self::NOSTO_ACCOUNT_PLACEHOLDER, $account->getName(), $urlTemplate);
        $src = str_replace(self::EMAIL_PLACEHOLDER, $order->getData(self::CUSTOMER_EMAIL), $src);
        return $src;
    }
}
